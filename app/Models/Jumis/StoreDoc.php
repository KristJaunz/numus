<?php

namespace App\Models\Jumis;

use App\Jumis;
use App\Models\Jumis\Structures\DocumentType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;


class StoreDoc extends Jumis
{
    protected $table        = 'StoreDoc';
    protected $primaryKey   = 'StoreDocID';

    protected $casts = [
        'DocDate'    => 'date',
        'CreateDate' => 'datetime',
        'DeliveryDate' => 'date',
    ];

    public static function boot()
    {
        parent::boot();

        self::creating(function($model){

            $model->PriceTaxIncluded = 1;
            $model->OmnivaStatus = 1;
            $model->FitekStatus = 1;
            $model->EAddressStatus = 1;
            $model->SemoStatus = 1;
            $model->DiscountCoeff = 0;



            CurrencyRate::firstOrCreate([
                'CurrencyID' => $model->CurrencyID,
                'RateDate' => $model->DocDate,
                'Rate' => 1,
                'RateVisual' => 1,
            ]);
        });

    }

    public function duplicates() {
        return $this->hasMany(self::class,['DocNo','DocNo',['Comment','Comment']]);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class,'PartnerID','PartnerID');
    }

    public function storeDocType(): BelongsTo
    {
        return $this->belongsTo(StoreDocType::class,'StoreDocTypeID','StoreDocTypeID');
    }

    public function tradeType(){
        return $this->belongsTo(StoreDocTradeType::class,'StoreDocTradeTypeID','StoreDocTradeTypeID');
    }

    public function currency(){
        return $this->belongsTo(Currency::class,'CurrencyID','CurrencyID');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(StoreDocStatus::class,'DocStatus','StatusID');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(StoreDocLine::class,'StoreDocID','StoreDocID');
    }

    public function storeDocLines()
    {
        return $this->lines();
    }

    public static function serialsList(): array {
        return Cache::remember('StoreDoc::listSerials',config('jumis.cache_time'),function () {
            return self::select('StoreDoc.DocNoSerial')->distinct()->pluck('DocNoSerial','DocNoSerial')->toArray();
        });
    }

    public static function purchasePartnerList(): array {

        return self::select('StoreDoc.PartnerID')
                        ->where('StoreDoc.StoreDocTypeID', DocumentType::PURCHASE_INVOICE)->with('Partner')->distinct()->get()->pluck('Partner.PartnerName','Partner.PartnerID')->toArray();

    }

}


