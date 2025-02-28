<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Settings extends Model
{
    protected $guarded = [];


    public static function read($key, $default = null)
    {
        return Cache::remember('Settings::read',now()->addMinutes(10),function () {
            return self::pluck('value', 'key')->toArray();
        })[$key] ?? $default;
    }
}
