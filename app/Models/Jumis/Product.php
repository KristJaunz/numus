<?php

namespace App\Models\Jumis;


use App\Jumis;
use App\Models\Jumis\Structures\DocumentType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
class Product extends Jumis
{
    protected $table = 'Product';
    protected $primaryKey = 'ProductID';

    protected static function booted()
    {
        parent::booted();
    }

    protected $casts = [
        'CreateDate' => 'datetime',
        'LatestPurchaseDate' => 'datetime',
        'PrevPurchaseDate' => 'datetime',
        'DiscountProductTo' => 'date',
        'DiscountCategoryTo' => 'date',
        'FinalDiscountTo' => 'date',
        'PurchaseDate' => 'date',
        'LastSellingDate' => 'date',
        'LastMovementDate' => 'date'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(self::class, 'ProductID', 'ProductID');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class, 'ProductID', 'ProductID');
    }

    public function remains(): HasMany
    {
        return $this->hasMany(ProductRemain::class, 'ProductID', 'ProductID');
    }

    public function warehouses()
    {
        return $this->hasMany(ProductWarehouse::class, 'ProductID', 'ProductID');
    }

    public function documentPurchaseInvoices(): HasMany
    {
        return $this->hasMany(StoreDocLine::class, 'ProductID', 'ProductID')
            ->where('StoreDocLine.StoreDocTypeID', DocumentType::PURCHASE_INVOICE->value);
    }

    public function documentInterWarehouseTransfer(): HasMany
    {
        return $this->hasMany(StoreDocLine::class, 'ProductID', 'ProductID')
            ->where('StoreDocLine.StoreDocTypeID', DocumentType::INTER_WAREHOUSE_TRANSFER->value);
    }

    public function documentReturnOfSoldProducts(): HasMany
    {
        return $this->hasMany(StoreDocLine::class, 'ProductID', 'ProductID')
            ->where('StoreDocLine.StoreDocTypeID', DocumentType::RETURN_OF_SOLD_PRODUCT->value);
    }

    public function documentBalances(): HasMany
    {
        return $this->hasMany(StoreDocLine::class, 'ProductID', 'ProductID')
            ->where('StoreDocLine.StoreDocTypeID', DocumentType::BALANCE_DOCUMENT->value);
    }

    public function documentWriteOffInvoices(): HasMany
    {
        return $this->hasMany(StoreDocLine::class, 'ProductID', 'ProductID')
            ->where('StoreDocLine.StoreDocTypeID', DocumentType::WRITE_OFF_DOCUMENT->value);
    }

    public function documentSalesInvoices(): HasMany
    {
        return $this->hasMany(StoreDocLine::class, 'ProductID', 'ProductID')
            ->where('StoreDocLine.StoreDocTypeID', DocumentType::SALES_INVOICE->value);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StoreDocLine::class, 'ProductID', 'ProductID');
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(ProductTypeDiscount::class, 'ProductID', 'ProductID');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'ProductTypeID', 'ProductTypeID');
    }

    public function originCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'OriginCountryID', 'CountryID');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'ProductUnitID', 'ProductUnitID');
    }

    public function purchasePartner()
    {
        return $this->belongsTo(Partner::class, 'PurchasePartnerID', 'PartnerID');
    }

    public function purchaseDocument()
    {
        return $this->belongsTo(StoreDoc::class, 'PurchaseDocID', 'StoreDocID');
    }


    public static function list(): array
    {
        return Cache::remember('Product::list', now()->addDay(), function () {
            return self::pluck('ProductName', 'ProductID')->toArray();
        });
    }


    public function class() {
        return $this->belongsTo(ProductClass::class,'ProductClassID','ProductClassID');
    }
}
