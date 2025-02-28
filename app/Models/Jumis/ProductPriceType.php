<?php

namespace App\Models\Jumis;

use App\Jumis;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductPriceType extends Jumis
{
    protected $table        = 'ProductPriceType';
    protected $primaryKey   = 'TypeID';

    public static function list(): array
    {
        return Cache::remember('ProductPriceType::list',now()->addDay(),function () {
            return self::pluck('TypeName','TypeID')->toArray();
        });
    }

}
