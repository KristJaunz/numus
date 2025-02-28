<?php

namespace App\Models\Jumis;


use App\Jumis;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class PartnerType extends Jumis
{
    protected $table        = 'PartnerType';
    protected $primaryKey   = 'TypeID';

    protected $casts = [
        'UpdateDate' => 'datetime',
        'CreateDate' => 'datetime'
    ];

    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class,'PartnerTypeID','TypeID');
    }

    public static function list(): array
    {
        return Cache::remember('PartnerType::list',now()->addDay(),function () {
            return self::all()->pluck('TypeName','TypeID')->toArray();
        });
    }
}
