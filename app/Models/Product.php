<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{

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
}
