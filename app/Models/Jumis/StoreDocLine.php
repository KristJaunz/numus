<?php

namespace App\Models\Jumis;


use App\Jumis;
use App\Models\Jumis\Structures\DocumentType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class StoreDocLine extends Jumis
{
    protected $table = 'StoreDocLine';
    protected $primaryKey = 'LineID';

    protected $casts = [
        'TimeLimit' => 'datetime',
        'PurchaseDate' => 'date',
    ];

    public static function boot()
    {
        parent::boot();

        self::creating(function ($documentLine) {
            $product = Product::where('ProductID', $documentLine->ProductID)->first();
            $document = StoreDoc::where('StoreDocID', $documentLine->StoreDocID)->first();

            if ($document->StoreDocTypeID == DocumentType::PURCHASE_INVOICE->value) {
                $documentLine->Cost = 0;
                $documentLine->Price = $documentLine->PricePurchase;
                $documentLine->PriceLVL = $documentLine->PricePurchase;

                if ($documentLine->VatRate > 0) {
                    $documentLine->PriceWithTax = 0;
                }
                else{
                    $documentLine->PriceWithTax = 0;
                }

            } elseif ($document->StoreDocTypeID == DocumentType::SALES_INVOICE->value) {




              /*

                 $priceWithVAT = $documentLine->PriceWithTax;

               if ($documentLine->VatRate > 0) {
                    $priceNoVat = $priceWithVAT / ((float) '1.'.(int) $documentLine->VatRate);
                } else {
                    $priceNoVat = $documentLine->PriceWithTax;
                }*/

                $documentLine->Cost = null;
                $documentLine->PricePurchase = null;
            }
            if ($document->StoreDocTypeID == DocumentType::RETURN_OF_SOLD_PRODUCT->value)
            {
                $pricePurchase = StoreDocLine::where('ProductID', $documentLine->ProductID)
                    ->select('PurchasePrice')
                    ->where('StoreDocTypeID', DocumentType::PURCHASE_INVOICE->value)
                    ->latest('StoreDocLineID')->first();

                $documentLine->Cost = $pricePurchase->Cost;
                $documentLine->PricePurchase = $pricePurchase->PricePurchase;
            }
            else {
                throw new \Exception('Sistēma pašlaik neatbalsta šī dokumenta labošanu.');
            }

            $documentLine->LinkedLine = 0;
            $documentLine->LineOrder = 0;
          //  $documentLine->DiscountPercent = 0;
            $documentLine->IsReverse = 0;

            $documentLine->StoreDocTypeID = $document->StoreDocTypeID;
            $documentLine->ProductUnitID = $product->ProductUnitID;
            $documentLine->ProductClassID = $product->ProductClassID;

            $documentLine->AmountFinal = $documentLine->PriceWithTax * $documentLine->Quantity;
        });

    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'ProductID', 'ProductID');
    }

    public function countryOrigin()
    {
        return $this->belongsTo(Country::class, 'ProductOriginCountryID', 'ProductOriginCountryID');
    }

    public function productType()
    {
        return $this->belongsTo(ProductType::class, 'ProductTypeID', 'ProductTypeID');
    }

    public function purchasePartner()
    {
        return $this->belongsTo(Partner::class, 'PurchasePartnerID', 'PartnerID');
    }


    public function in(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'StoreInID', 'PartnerID');
    }

    public function out(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'StoreOutID', 'PartnerID');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(StoreDoc::class, 'StoreDocID', 'StoreDocID');
    }

    public function purchaseDocument(): BelongsTo
    {
        return $this->belongsTo(StoreDoc::class, 'PurchaseDocID', 'StoreDocID');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(StoreDocType::class, 'StoreDocTypeID', 'StoreDocTypeID');
    }

    public function links(): HasMany
    {
        return $this->hasMany(StoreDocLineLink::class, 'ToID', 'LineID');
    }
}
