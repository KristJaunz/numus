<?php

namespace App;

use App\Models\Jumis\StoreDoc;
use App\Models\Jumis\StoreDocLine;
use App\Models\Jumis\Structures\DocumentStatus;
use App\Models\Jumis\Structures\DocumentType;
use App\Models\Shop;
use App\Models\Tender;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
//use App\Mail\TransactionFailureMail; // Example mail class

class SQLImport
{

    const MAX_RETRIES = 3;
    const DB_CATCHUP_DELAY = 3;

    const RETRY_DELAY = 2;

    const MAX_DELAY = 120;

    const BACKOFF_FACTOR = 2;

    public  function importStoreDocWithRetries(Tender $record):bool
    {
        if (!$record->doc_no || !$record->doc_no_serial || !$record->doc_date) {
            \App\Models\Log::write($record,'Nav nepieciešamie lauki darijuma ierakstā. Pārtraucam importu');
            return false;
        }

        $identifier = $record->file . ' : '. $record->doc_no_serial . ' - '. $record->doc_no;

        $attempt = 0;

        $storeDocType = DocumentType::SALES_INVOICE->value;

        if (str($record->doc_no_serial)->contains('Atgr.')) {

            \App\Models\Log::write($record,'Darījums ir atgriešanas dokuments. Izlaižam.');
            return false;
        }

        $shop = Shop::list()->where('doc_serial', $record->doc_no_serial)->first();

        if (!$shop) {
            $shop = Shop::list()->where('doc_serial_return', $record->doc_no_serial)->first();
        }

        if (!$shop) {
            \App\Models\Log::write($record,'Veikals ar šādu dokumenta sēriju nav atrasts.');
            return false;
        }


        $storeAddress = $shop->address;
        $storeOutId = $shop->partner_id;
        $docStatus = DocumentStatus::STARTED->value;
        $companyVat = '40103127729';

        $connection = DB::connection('sqlsrv');

        while ($attempt < self::MAX_RETRIES) {

            $storeDocID = null;

            try {

                $connection->beginTransaction();

                // Insert StoreDoc

                $storeDoc = StoreDoc::create([
                    'DocNo' => $record->doc_no,
                    'DocNoSerial' => $record->doc_no_serial,
                    'DocDate' => Carbon::parse($record->doc_date)->toDateString(),
                    'DeliveryDate' => $record->doc_date,
                    'StoreDocTypeID' => $storeDocType,
                    'StoreDocTradeTypeID' => 1,
                    'PartnerID' => $record->partner_id,
                    'StoreAddress' => $storeAddress,
                    'CompanyVatCountryID' => 7,
                    'CompanyVatNo' => $companyVat,
                    'CurrencyID' => 19,
                    'DiscountPercent' => $record->tender_discount,
                    'TaxTypeID' => 1,
                    'PriceTaxIncluded' => 1,
                    'DocStatus' => $docStatus,
                    'Comments' => $record->comment,

                ]);

                $storeDocID = $storeDoc->StoreDocID;

                $identifier = $storeDocID. " / " . $identifier;

                foreach ($record->docLines as $line) {
                    StoreDocLine::create([
                        'StoreDocID' => $storeDocID,
                        'ProductID' => $line->i,
                        'Quantity' => $line->q,
                        'PriceWithTax' => $line->pb,
                        'DiscountPercent' => $line->d,
                        'VatRate' => $line->r,
                        'StoreOutID' => $storeOutId,
                    ]);
                }

                $connection->commit();

                $record->store_doc_id = $storeDocID;
                $record->save();

                $record->delete();

                \App\Models\Log::write($record,'Darījums veiksmīgi importēts!');

                return true;
            } catch (QueryException $e) {
                $connection->rollBack();

                sleep(self::DB_CATCHUP_DELAY); // Allow DB to catch up

                if (!$this->isTransactionConsistent($storeDocID, $record)) {
                    \App\Models\Log::write($record,'Atrasti nepilnīgi dati darijumam . Notiek tīrīšana');

                    $this->cleanupPartialTransaction($storeDocID,$record, $identifier);
                }

                if ($this->isRetryableError($e)) {
                    $attempt++;
                    $delay = min(self::RETRY_DELAY * pow(self::BACKOFF_FACTOR, $attempt), self::MAX_DELAY);

                    \App\Models\Log::write($record,"Notika kļūda no kuras var atgūties. Mēģinam velreiz ($attempt/". self::MAX_RETRIES .") pēc $delay sekundēm. Kļūda: {$e->getMessage()}");

                    if ($attempt >= self::MAX_RETRIES) {

                        \App\Models\Log::write($record,"Maksimālais atkārtotu mēģinājumu skaits sasniegts");


                        $this->sendFailureAlert($record, $e);
                        return false;

                    }

                    sleep($delay);
                } else
                {
                    \App\Models\Log::write($record,"Atkārtoti neapstrādājama kļūda konstatēta. Kļūda: {$e->getMessage()}");

                    $this->sendFailureAlert($record, $e);
                    return false;
                }
            }
            catch (\Exception $e) {
                $connection->rollBack();

                sleep(self::DB_CATCHUP_DELAY);

                \App\Models\Log::write($record,"Konstatēta neparedzēta kļūda. Kļūda: {$e->getMessage()}");

                if (!$this->isTransactionConsistent($storeDocID, $record)) {

                    \App\Models\Log::write($record,"Atrasti nepilnīgi dati darijumam . Notiek tīrīšana");

                    $this->cleanupPartialTransaction($storeDocID, $record,$identifier);
                }

                $this->sendFailureAlert($record, $e);
                return false;
            }
        }

        return false;
    }

    function isTransactionConsistent($storeDocID,Tender $record): bool
    {
        if (!$storeDocID) {
            return false;
        }

        $storeDoc = StoreDoc::where('StoreDocID', $storeDocID)->exists();

        if ($storeDoc) {
            return false;
        }

        $lines = StoreDocLine::where('StoreDocID', $storeDocID)->exists();

        if ($lines) {
            return false;
        }

        return true;
    }

    function cleanupPartialTransaction($storeDocID,Tender $record, $identifier): void
    {
        if (!$storeDocID) {
            return;
        }

        $attempt = 0;

        while ($attempt < self::MAX_RETRIES) {
            try {

                StoreDoc::where('StoreDocID', $storeDocID)->delete();
                StoreDocLine::where('StoreDocID', $storeDocID)->delete();

                \App\Models\Log::write($record,"Veiksmīgi notīrīts daļēji importētais darījums");
                return;

            } catch (\Exception $e) {

                \App\Models\Log::write($record,"Mēģinājums {$attempt} notīrīt nepabeigtos ierakstus neizdevās. Kļūda: {$e->getMessage()}");

                $attempt++;

                $delay = min(self::RETRY_DELAY * pow(self::BACKOFF_FACTOR, $attempt), self::MAX_DELAY);

                if ($attempt < self::MAX_RETRIES) {
                    \App\Models\Log::write($record,"Atkārtoti mēģinām notīrīt ierakstus pēc {$delay} sekundēm");
                    sleep($delay);
                }
            }
        }

        \App\Models\Log::write($record,"Neizdevās notīrīt daļējos ierakstus pēc ". self::MAX_RETRIES." mēģinājumiem.");
    }


    function sendFailureAlert($storeDocData, \Exception $e)
    {
        $emailData = [
            'storeDocID' => $storeDocData['StoreDocID'],
            'errorMessage' => $e->getMessage(),
            'stackTrace' => $e->getTraceAsString(),
            'storeDocData' => json_encode($storeDocData),
        ];

        // Send an email alert to administrators about the failure
        try {
          //  Mail::to('admin@example.com')->send(new TransactionFailureMail($emailData));
        } catch (\Exception $mailException) {
            Log::error("Failed to send failure alert email: " . $mailException->getMessage());
        }
    }


    private function isRetryableError(QueryException $e): bool
    {
        $errorCodes = [
            '1205',     // Deadlock
            '10060',    // Timeout
            '10061',    // Network error
            '4060',     // Database not available
            '20000'     // Lock wait timeout exceeded (can vary)
        ];

        if (in_array($e->getCode(), $errorCodes)) {
            return true;
        }

        if (str_contains($e->getMessage(), 'deadlock') || str_contains($e->getMessage(), 'timeout')) {
            return true;
        }

        return false;
    }

}
