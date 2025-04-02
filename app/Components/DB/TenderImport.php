<?php

namespace App\Components\DB;

use App\Models\Jumis\StoreDoc;
use App\Models\Jumis\StoreDocLine;
use App\Models\Jumis\Structures\DocumentStatus;
use App\Models\Jumis\Structures\DocumentType;
use App\Models\Product;
use App\Models\Settings;
use App\Models\Shop;
use App\Models\Tender;
use Illuminate\Database\QueryException;
use Illuminate\Session\Store;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class TenderImport
{
    const MAX_RETRIES = 3;
    const DB_CATCHUP_DELAY = 3;

    const RETRY_DELAY = 2;

    const MAX_DELAY = 120;

    const BACKOFF_FACTOR = 2;

    public function importStoreDocWithRetries(Tender $record): bool
    {
        $isDeleted = Tender::select('deleted_at', 'id')->where('id', $record->id)->withTrashed()->first();

        if ($isDeleted !== null) {
            if ($isDeleted->deleted_at !== null) {
                \App\Models\Log::write($record, 'Ieraksts jau ir importēts!');
                return false;
            }
        }

        if (!$record->doc_no || !$record->doc_no_serial || !$record->doc_date) {
            \App\Models\Log::write($record, 'Nav nepieciešamie lauki darijuma ierakstā. Pārtraucam importu');
            return false;
        }

        $identifier = $record->file.' : '.$record->doc_no_serial.' - '.$record->doc_no;

        $attempt = 0;

        $storeDocType = DocumentType::SALES_INVOICE->value;

        if (str($record->doc_no_serial)->contains('Atgr.')) {

            \App\Models\Log::write($record, 'Darījums ir atgriešanas dokuments. Izlaižam.');
            return false;
        }

        $shop = Shop::list()->where('doc_serial', $record->doc_no_serial)->first();

        if (!$shop) {
            $shop = Shop::list()->where('doc_serial_return', $record->doc_no_serial)->first();
        }

        if (!$shop) {
            \App\Models\Log::write($record, 'Veikals ar šādu dokumenta sēriju nav atrasts.');
            return false;
        }

        $storeAddress = $shop->address;
        $storeOutId = $shop->partner_id;
        $docStatus = DocumentStatus::STARTED->value;
        $companyVat = Settings::read('company_reg_no');

        $connection = DB::connection('sqlsrv');


        // $productConf = Product::read($record->i);


        while ($attempt < self::MAX_RETRIES) {

            $storeDocID = null;

            try {

                $connection->beginTransaction();

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

                $identifier = $storeDocID." / ".$identifier;

                foreach ($record->docLines as $line) {


                    $product = $line->i;
                    $quantity = $line->q;
                    $catalogPrice = $line->pb;
                    $taxRate = $line->r;
                    $priceWithTaxAndDiscount = $line->p;
                    $discount = $this->getDiscount($line->d);
                    $shop = $storeOutId;


                    if ($line->i == '17240') {
                        $taxRate = 0;

                    }


                    /*  if ($productConf !== null) {
                          if ($productConf->tax_rate >= 0) {
                              $taxRate = $productConf->tax_rate;
                          }
                          elseif ($productConf->tax_rate == 'n/a') {
                              $taxRate = null;
                          }
                      }*/


                    /*$documentLine->Price = $priceNoVat;
                      $documentLine->PriceLVL = $priceNoVat;
                      $documentLine->PriceWithTax = $priceWithVAT;*/


                    if ($taxRate > 0) {
                        $priceNoVat = $catalogPrice / ((float) '1.'.(int) $taxRate);
                    } else {
                        $priceNoVat = $catalogPrice;
                    }

                    StoreDocLine::create([
                        'StoreDocID' => $storeDocID,
                        'ProductID' => $product,
                        'Quantity' => $quantity,

                        'Price' => $priceNoVat,
                        'PriceLVL' => $priceNoVat,

                        'VatRate' => $taxRate,

                        'DiscountPercent' => $discount,
                        'PriceWithTax' => $priceWithTaxAndDiscount,
                        'StoreOutID' => $shop,
                    ]);

                }

                $connection->commit();

                $record->store_doc_id = $storeDocID;
                $record->save();

                $record->delete();

                \App\Models\Log::write($record, 'Darījums veiksmīgi importēts!');

                return true;
            } catch (QueryException $e) {
                $connection->rollBack();

                sleep(self::DB_CATCHUP_DELAY); // Allow DB to catch up

                if (!$this->isTransactionConsistent($storeDocID, $record)) {
                    \App\Models\Log::write($record, 'Atrasti nepilnīgi dati darijumam . Notiek tīrīšana');

                    $this->cleanupPartialTransaction($storeDocID, $record, $identifier);
                }

                if (SqlServer::isRetryableError($e)) {
                    $attempt++;
                    $delay = min(self::RETRY_DELAY * pow(self::BACKOFF_FACTOR, $attempt), self::MAX_DELAY);

                    \App\Models\Log::write($record,
                        "Notika kļūda no kuras var atgūties. Mēģinam velreiz ($attempt/".self::MAX_RETRIES.") pēc $delay sekundēm. Kļūda: {$e->getMessage()}");

                    if ($attempt >= self::MAX_RETRIES) {

                        \App\Models\Log::write($record, "Maksimālais atkārtotu mēģinājumu skaits sasniegts");


                        $this->sendFailureAlert($record, $e);
                        return false;

                    }

                    sleep($delay);
                } else {
                    \App\Models\Log::write($record,
                        "Atkārtoti neapstrādājama kļūda konstatēta. Kļūda: {$e->getMessage()}");

                    $this->sendFailureAlert($record, $e);
                    return false;
                }
            } catch (\Exception $e) {
                $connection->rollBack();

                sleep(self::DB_CATCHUP_DELAY);

                \App\Models\Log::write($record, "Konstatēta neparedzēta kļūda. Kļūda: {$e->getMessage()}");

                if (!$this->isTransactionConsistent($storeDocID, $record)) {

                    \App\Models\Log::write($record, "Atrasti nepilnīgi dati darijumam . Notiek tīrīšana");

                    $this->cleanupPartialTransaction($storeDocID, $record, $identifier);
                }

                $this->sendFailureAlert($record, $e);
                return false;
            }
        }

        return false;
    }

    public function resync(Tender $record): bool
    {

        if ($record->store_doc_id > 0 and !empty($record->store_doc_id)) {
            \App\Models\Log::write($record, 'Ieraksts nav sinhronizēts!!');
            return false;
        }


        $storeDoc = StoreDoc::where('StoreDocID', $record->store_doc_id)->first();

        if ($storeDoc == null) {
            \App\Models\Log::write($record, 'Darījums nav atrasts tildes jumī!!');
            return false;
        }

        if ($storeDoc->DocStatus == 6) { // Kontēts
            \App\Models\Log::write($record, 'Darījums ir kontēts');
            return false;
        }

        $storeDoc->DocStatus = 1;
        $storeDoc->save();


        foreach ($record->docLines as $line) {

            $product = $line->i;
            $quantity = $line->q;
            $catalogPrice = $line->pb;
            $taxRate = $line->r;
            $priceWithTaxAndDiscount = $line->p;
            $discount = $this->getDiscount($line->d);

            if ($line->i == '17240') {
                $taxRate = 0;
            }

            if ($taxRate > 0) {
                $priceNoVat = $catalogPrice / ((float) '1.'.(int) $taxRate);
            } else {
                $priceNoVat = $catalogPrice;
            }

            StoreDocLine::query()
                ->where('StoreDocID', $storeDoc->StoreDocID)
                ->where('ProductID', $product)
                ->update([
                    'Quantity' => $quantity,
                    'Price' => $priceNoVat,
                    'PriceLVL' => $priceNoVat,
                    'VatRate' => $taxRate,
                    'DiscountPercent' => $discount,
                    'PriceWithTax' => $priceWithTaxAndDiscount,
                ]);

        }
        return true;

    }

    function isTransactionConsistent($storeDocID, Tender $record): bool
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

    function cleanupPartialTransaction($storeDocID, Tender $record, $identifier): void
    {
        if (!$storeDocID) {
            return;
        }

        $attempt = 0;

        while ($attempt < self::MAX_RETRIES) {
            try {

                StoreDoc::where('StoreDocID', $storeDocID)->delete();
                StoreDocLine::where('StoreDocID', $storeDocID)->delete();

                \App\Models\Log::write($record, "Veiksmīgi notīrīts daļēji importētais darījums");
                return;

            } catch (\Exception $e) {

                \App\Models\Log::write($record,
                    "Mēģinājums {$attempt} notīrīt nepabeigtos ierakstus neizdevās. Kļūda: {$e->getMessage()}");

                $attempt++;

                $delay = min(self::RETRY_DELAY * pow(self::BACKOFF_FACTOR, $attempt), self::MAX_DELAY);

                if ($attempt < self::MAX_RETRIES) {
                    \App\Models\Log::write($record, "Atkārtoti mēģinām notīrīt ierakstus pēc {$delay} sekundēm");
                    sleep($delay);
                }
            }
        }

        \App\Models\Log::write($record, "Neizdevās notīrīt daļējos ierakstus pēc ".self::MAX_RETRIES." mēģinājumiem.");
    }


    function sendFailureAlert($storeDocData, \Exception $e)
    {
        $emailData = [
            'storeDocID' => $storeDocData['StoreDocID'],
            'errorMessage' => $e->getMessage(),
            'stackTrace' => $e->getTraceAsString(),
            'storeDocData' => json_encode($storeDocData),
        ];

        try {
            //  Mail::to('admin@example.com')->send(new TransactionFailureMail($emailData));
        } catch (\Exception $mailException) {
            Log::error("Failed to send failure alert email: ".$mailException->getMessage());
        }
    }


    public function getDiscount($discount)
    {
        return (float) $discount > 0 ? number_format((float) $discount, 4) : null;
    }

}
