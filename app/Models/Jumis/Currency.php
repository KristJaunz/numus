<?php

namespace App\Models\Jumis;

use App\Jumis;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Currency extends Jumis
{
    protected $table = 'Currency';
    protected $primaryKey = 'CurrencyID';

    protected $casts = [
        'RateScale' =>  'float',
        'UpdateDate' => 'datetime',
        'CreateDate' => 'datetime'
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class, 'CurrencyID', 'CurrencyID');
    }


    public static function list(): array
    {
        return Cache::remember('Currency::list',now()->addDay(),function () {
            return self::all()->pluck('Description', 'CurrencyID')->toArray();
        });
    }

    public static function getCurrencyCodes(): array
    {
        return self::all()->pluck('CurrencyCode', 'CurrencyID')->toArray();
    }

    public static function codeToSymbol(string $code)
    {
        $codes = [
            'EUR' => '€',
            'USD' => '$',
        ];

        return $codes[$code] ?? $code;
    }
}

