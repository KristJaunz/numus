<?php

namespace App\Models\Jumis;


use App\Jumis;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class ProductWarehouse extends Jumis
{
    protected $table = 'ProductWarehouse';
    protected $primaryKey = 'ProductWarehouseID';


    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'ProductID', 'ProductID');
    }

    public function remains(): HasMany
    {
        return $this->hasMany(ProductRemain::class, 'ProductID', 'ProductID');
    }

    public function partner() {
        return $this->belongsTo(Partner::class,'PartnerID','PartnerID');
    }

    public function unit() {
        return $this->belongsTo(ProductUnit::class,'ProductUnitID','ProductUnitID');
    }
}
