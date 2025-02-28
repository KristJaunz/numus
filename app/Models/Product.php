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
        return self::list()[$key] ?? $default;
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'ProductID');
    }
}
