<?php

namespace App\Models\Jumis;

use App\Engine\Jumis\JumisModel;
use App\Jumis;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductPrice extends Jumis
{
    protected $table      = 'ProductPrice';
    protected $primaryKey = 'ProductPriceID';

    protected $casts = [
        'PurchaseDate' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class,'ProductID','ProductID');
    }

    public function type(): BelongsTo
    {
       return $this->belongsTo(ProductPriceType::class,'TypeID','TypeID');
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(ProductTypeDiscount::class,'ProductID','ProductID');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class,'PartnerID','PartnerID');
    }

    public function currency(): BelongsTo {
        return $this->belongsTo(Currency::class, 'CurrencyID','CurrencyID');
    }

}
