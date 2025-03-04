<?php

namespace App\Components;

use App\Models\DocLine;
use App\Models\Tender;
use Exception;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class ProcessTenderXML
{
    public static function run($xmlString, $file)
    {
        DB::beginTransaction();  // Start the transaction

        try {
            $xml = new SimpleXMLElement($xmlString);

            // Prepare tender data
            $tenderData = [
                'file' => $file,
                'store_doc_id' => (int) $xml->StoreDocID,
                'doc_no' => (string) $xml->DocNo,
                'doc_no_serial' => (string) $xml->DocNoSerial,
                'doc_date' => (string) $xml->DocDate,
                'currency_code' => (string) $xml->CurrencyCode,
                'currency_rate' => (float) $xml->CurrencyRate,
                'amount_cash' => (float) $xml->AmountCash,
                'amount_card' => (float) $xml->AmountCard,
                'tender_discount' => (float) $xml->TenderDiscount,
            ];

            // Insert the tender data
            $tender = Tender::create($tenderData);

            // Parse <DocLineXML> (decode & extract <r> elements)
            $docLinesXml = html_entity_decode($xml->DocLineXML);
            $docLines = new SimpleXMLElement("<root>$docLinesXml</root>");

            // Prepare doc lines data in bulk
            $docLinesData = [];
            foreach ($docLines->r as $line) {
                $attributes = [
                    'tender_id' => $tender->id,
                    'created_at' => now(),
                ];


                foreach ($line->attributes() as $key => $value) {
                    $attributes[$key] = (string) $value;
                }
                $docLinesData[] = $attributes;

            }

            // Insert the doc lines in bulk
            DocLine::insert($docLinesData);

            // Commit the transaction
            DB::commit();

            return true;  // Return true on success
        } catch (Exception $e) {
            DB::rollBack();  // Rollback the transaction on error
            // Log the error if needed, for debugging purposes
            \Log::error('Error processing '.$file.' : ' . $e->getMessage());
            return false;  // Return false on error
        }
    }

}
