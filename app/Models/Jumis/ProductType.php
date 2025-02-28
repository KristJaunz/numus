<?php

namespace App\Models\Jumis;

use App\Jumis;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Support\Facades\Cache;


class ProductType extends Jumis
{
    protected $table = 'ProductType';
    protected $primaryKey = 'ProductTypeID';


    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'ProductTypeID', 'ProductTypeID');
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(ProductTypeDiscount::class, 'ProductTypeID', 'ProductTypeID');
    }

    public static function list(): array {
        return Cache::remember('ProductType::list',config('jumis.cache_time'),function () {
           return self::pluck('ProductTypeName', 'ProductTypeID')->toArray();
        });
    }



}
