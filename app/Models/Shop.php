<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Shop extends Model
{
    protected $guarded = [];

    public static function list()
    {
        return Cache::remember('Shops::list',now()->addMinute(),function () {
            return self::all();
        });
    }
}
