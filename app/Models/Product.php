<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    protected $guarded = [];

    public static function list()
    {
        return Cache::remember('Product::read',now()->addMinute(),function () {
            return self::all();
        });
    }

    public static function read($key, $default = null)
    {
        return self::list()->where('ProductID',$key)->first();
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Jumis\Product::class, 'product_id', 'ProductID');
    }
}
