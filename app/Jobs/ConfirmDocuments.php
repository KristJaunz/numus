<?php

namespace App\Jobs;

use App\Components\DB\SqlServer;
use App\Models\Jumis\StoreDoc;
use App\Models\Jumis\Structures\DocumentStatus;
use App\Models\Log;
use App\Models\Shop;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Queue\Queueable;

class ConfirmDocuments implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        try {
            $shops = Shop::list();

            foreach ($shops as $shop) {
                $documents = StoreDoc::where('DocNoSerial', $shop->doc_serial)
                    ->with('lines')
                    ->where('DocStatus', DocumentStatus::STARTED->value)
                    ->get();

                foreach ($documents as $document) {
                    $this->processDocument($document);
                }
            }
        } catch (\Throwable $e) {
            Log::write(null, "Neparedzēta kļūda apstrādājot dokumentus veikalā {$shop->id}: {$e->getMessage()}");
            $this->fail($e);
        }
    }

    protected function processDocument($document): void
    {
        if ($this->confirmDocument($document)) {
            Log::write(null, "Dokuments {$document->id} ({$document->DocNoSerial}-{$document->DocNo}) veiksmīgi apstiprināts.");
            return; // All good — nothing more to do.
        }

        Log::write(null, "Dokumentam {$document->id} ({$document->DocNoSerial}-{$document->DocNo}) neizdevās apstiprināt. Mēģinu apstiprināt katru rindu atsevišķi.");

        $allLinesConfirmed = $this->confirmAllLines($document);

        $document->DocStatus = DocumentStatus::ENTERED;
        $document->save();

        if ($allLinesConfirmed) {
            Log::write(null, "Visas rindas dokumentam {$document->id} ({$document->DocNoSerial}-{$document->DocNo}) apstiprinātas, dokuments iestatīts uz ENTERED.");
        } else {
            Log::write(null, "Ne visas rindas dokumentam {$document->id} ({$document->DocNoSerial}-{$document->DocNo}) varēja apstiprināt, dokuments iestatīts uz ENTERED.");
        }
    }

    protected function confirmDocument($document): bool
    {
        return $this->updateDocumentStatusWithRetry($document, DocumentStatus::CONFIRMED->value);
    }

    protected function confirmAllLines($document): bool
    {
        $allSuccessful = true;

        foreach ($document->lines as $line) {
            $confirmed = $this->confirmLine($line);
            if (!$confirmed) {
                $allSuccessful = false;
            }
        }

        return $allSuccessful;
    }

    protected function confirmLine($line): bool
    {
        $attempt = 0;
        $maxRetries = 3;

        while ($attempt < $maxRetries) {
            try {
                $line->DocStatus = DocumentStatus::CONFIRMED->value;
                $line->save();
                return true; // Success.
            } catch (QueryException $e) {
                if (SqlServer::isRetryableError($e)) {
                    $attempt++;
                    $this->logRetry("rinda", $line->id, $attempt);
                    $this->exponentialBackoff($attempt);
                } else {
                    Log::write(null, "Neatjaunojama kļūda apstiprinot rindu {$line->id} ({$line->DocNoSerial}-{$line->DocNo}): {$e->getMessage()}");
                    return false;
                }
            } catch (\Exception $e) {
                Log::write(null, "Neparedzēta kļūda apstiprinot rindu {$line->id} ({$line->DocNoSerial}-{$line->DocNo}): {$e->getMessage()}");
                return false;
            }
        }

        Log::write(null, "Neizdevās apstiprināt rindu {$line->id} ({$line->DocNoSerial}-{$line->DocNo}) pēc {$maxRetries} mēģinājumiem.");
        return false;
    }

    protected function updateDocumentStatusWithRetry($document, $status): bool
    {
        $attempt = 0;
        $maxRetries = 3;

        while ($attempt < $maxRetries) {
            try {
                $document->DocStatus = $status;
                $document->save();
                return true; // Success.
            } catch (QueryException $e) {
                if (SqlServer::isRetryableError($e)) {
                    $attempt++;
                    $this->logRetry("dokumenta statusa atjaunināšana", $document->id, $attempt);
                    $this->exponentialBackoff($attempt);
                } else {
                    Log::write(null, "Neatjaunojama kļūda atjauninot dokumenta {$document->id} ({$document->DocNoSerial}-{$document->DocNo}) statusu: {$e->getMessage()}");
                    return false;
                }
            } catch (\Exception $e) {
                Log::write(null, "Neparedzēta kļūda atjauninot dokumenta {$document->id} ({$document->DocNoSerial}-{$document->DocNo}) statusu: {$e->getMessage()}");
                return false;
            }
        }

        Log::write(null, "Neizdevās atjaunināt dokumenta {$document->id} ({$document->DocNoSerial}-{$document->DocNo}) statusu pēc {$maxRetries} mēģinājumiem.");
        return false;
    }

    protected function logRetry(string $type, $id, int $attempt): void
    {
        Log::write(null, "Atkārtojam {$type} ID: {$id} ({$document->DocNoSerial}-{$document->DocNo}), mēģinājums: {$attempt}");
    }

    protected function exponentialBackoff(int $attempt): void
    {
        $baseDelay = 2; // seconds
        $maxDelay = 30; // seconds
        $delay = min($baseDelay * pow(2, $attempt), $maxDelay);
        sleep($delay);
    }
}
