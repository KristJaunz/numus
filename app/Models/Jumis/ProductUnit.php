<?php

namespace App\Models\Jumis;

use App\Engine\Jumis\JumisModel;
use App\Jumis;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;


class ProductUnit extends Jumis
{
    protected $table = 'ProductUnit';
    protected $primaryKey = 'ProductUnitID';

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'ProductUnitID', 'ProductUnitID');
    }

    public static function list(): array {
        return Cache::remember('ProductUnit::list',config('jumis.cache_time'), function () {
            return self::pluck('ProductUnitName','ProductUnitID')->toArray();
        });
    }

}
