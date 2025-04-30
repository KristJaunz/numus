<?php

namespace App\Models\Jumis\Structures;

enum DocumentType: int
{
    CASE PURCHASE_INVOICE = 1;
    CASE RETURN_OF_SOLD_PRODUCT = 2;
    CASE SALES_INVOICE = 3;
    CASE WRITE_OFF_DOCUMENT = 4;
    CASE INTER_WAREHOUSE_TRANSFER = 7;
    CASE RETURN_OF_PURCHASED_PRODUCT = 9;
    CASE BALANCE_DOCUMENT = 12;
    CASE REVALUATION_DOCUMENT = 14;
    CASE PURCHASE_ORDER = 20;
    CASE SALES_ORDER = 21;
    CASE BILL = 25;

    public function label(): string {
        return match($this) {
            self::PURCHASE_INVOICE => __('Purchase Invoice'),
            self::RETURN_OF_SOLD_PRODUCT => __('Return Of Sold Product'),
            self::SALES_INVOICE => __('Sales Invoice'),
            self::WRITE_OFF_DOCUMENT => __('Write-Off Document'),
            self::INTER_WAREHOUSE_TRANSFER => __('Inter-Warehouse Transfer'),
            self::RETURN_OF_PURCHASED_PRODUCT => __('Return Of Purchased Product'),
            self::BALANCE_DOCUMENT => __('Balance Document'),
            self::REVALUATION_DOCUMENT => __('Revaluation Document'),
            self::PURCHASE_ORDER => __('Purchase Order'),
            self::SALES_ORDER => __('Sales Order'),
            self::BILL => __('Bill'),
        };
    }

    public static function list(): array {
        return [
            self::PURCHASE_INVOICE->value => __('Purchase Invoice'),
            self::RETURN_OF_SOLD_PRODUCT->value => __('Return Of Sold Product'),
            self::SALES_INVOICE->value => __('Sales Invoice'),
            self::WRITE_OFF_DOCUMENT->value => __('Write-Off Document'),
            self::INTER_WAREHOUSE_TRANSFER->value => __('Inter-Warehouse Transfer'),
            self::RETURN_OF_PURCHASED_PRODUCT->value => __('Return Of Purchased Product'),
            self::BALANCE_DOCUMENT->value => __('Balance Document'),
            self::REVALUATION_DOCUMENT->value => __('Revaluation Document'),
            self::PURCHASE_ORDER->value => __('Purchase Order'),
            self::SALES_ORDER->value => __('Sales Order'),
            self::BILL->value => __('Bill'),
        ];
    }

    public static function name(DocumentType|int $documentType) {

        if ($documentType instanceof DocumentType) {
            $documentType = $documentType->value;
        }

        return self::list()[$documentType] ?? __('Unknown Document Type');
    }



}
