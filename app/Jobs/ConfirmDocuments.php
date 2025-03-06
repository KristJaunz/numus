<?php

namespace App\Jobs;

use App\Components\DB\SqlServer;
use App\Models\Jumis\StoreDoc;
use App\Models\Jumis\Structures\DocumentStatus;
use App\Models\Log;
use App\Models\Settings;
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
                StoreDoc::where('DocNoSerial', $shop->doc_serial)
                    ->with('lines')
                    ->where('DocStatus', DocumentStatus::STARTED->value)
                    ->chunk(Settings::read('doc_confirm_chunk',100),function ($documents) {
                        foreach ($documents as $document) {

                            if ($this->confirmDocument($document, DocumentStatus::CONFIRMED->value)) {
                                Log::write(null, "Dokuments ({$document->DocNoSerial}-{$document->DocNo}) veiksmīgi apstiprināts.");
                                return;
                            }

                            Log::write(null, "Dokuments ({$document->DocNoSerial}-{$document->DocNo}) neizdevās apstiprināt. Mēģinam daļēju apstiprināšanu!");

                            $this->confirmAllLines($document);

                            $document->DocStatus = DocumentStatus::ENTERED;
                            $document->save();

                            Log::write(null, "Dokuments ({$document->DocNoSerial}-{$document->DocNo}) apstrādāts daļēji!");
                        }
                    });
            }
        }
        catch (\Throwable $e)
        {
            Log::write(null, "Neparedzēta kļūda apstrādājot dokumentus veikalā {$shop->doc_serial}: {$e->getMessage()}");
            $this->fail($e);
        }
    }

    protected function confirmAllLines($document): bool
    {
        $allSuccessful = true;

        foreach ($document->lines as $line) {
            $confirmed = $this->confirmLine($document,$line);
            if (!$confirmed) {
                $allSuccessful = false;
            }
        }

        return $allSuccessful;
    }

    protected function confirmLine($document,$line): bool
    {
        $attempt = 0;
        $maxRetries = 3;

        while ($attempt < $maxRetries) {
            try {
                $line->LinkedLine = 1;
                $line->save();
                return true;
            }
            catch (QueryException $e) {
                if (SqlServer::isRetryableError($e))
                {
                    $attempt++;
                    $this->pauseExecution($attempt);
                }
                else
                {
                    return false;
                }
            }
            catch (\Exception $e) {
                return false;
            }
        }
        return false;
    }

    protected function confirmDocument($document, $status): bool
    {
        $attempt = 0;
        $maxRetries = 3;

        while ($attempt < $maxRetries)
        {
            try
            {
                $document->DocStatus = $status;
                $document->save();
                return true;
            }
            catch (QueryException $e) {
                if (SqlServer::isRetryableError($e)) {
                    $attempt++;
                    $this->logRetry("dokumenta statusa atjaunināšana", $document->id, $attempt,$document);
                    $this->pauseExecution($attempt);
                } else
                {
                    Log::write(null, "Neatjaunojama kļūda atjauninot dokumenta {$document->id} ({$document->DocNoSerial}-{$document->DocNo}) statusu: {$e->getMessage()}");
                    return false;
                }
            }
            catch (\Exception $e)
            {
                Log::write(null, "Neparedzēta kļūda atjauninot dokumenta {$document->id} ({$document->DocNoSerial}-{$document->DocNo}) statusu: {$e->getMessage()}");
                return false;
            }
        }

        Log::write(null, "Neizdevās atjaunināt dokumenta {$document->id} ({$document->DocNoSerial}-{$document->DocNo}) statusu pēc {$maxRetries} mēģinājumiem.");
        return false;
    }

    protected function logRetry(string $type, $id, int $attempt, $document): void
    {
        Log::write(null, "Atkārtojam {$type} ID: {$id} , mēģinājums: {$attempt}");
    }

    protected function pauseExecution(int $attempt): void
    {
        $baseDelay = 2;
        $maxDelay = 30;
        $delay = min($baseDelay * pow(2, $attempt), $maxDelay);
        sleep($delay);
    }
}
