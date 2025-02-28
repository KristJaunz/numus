<?php

namespace App\Models\Jumis;

use App\Jumis;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Partner extends Jumis
{
    protected $table        = 'Partner';
    protected $primaryKey   = 'PartnerID';

    protected $casts = [
        'CreateDate' => 'datetime',
        'UpdateDate' => 'datetime',
        'PhysicalPersonBirthDate' => 'datetime',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(StoreDoc::class,'PartnerID','PartnerID');
    }

    public function type(): BelongsTo {
        return $this->belongsTo(PartnerType::class,'PartnerTypeID','TypeID');
    }

    public static function list(): array
    {
        return Cache::remember('Partner::list',now()->addDay(),function () {
            return self::all()->pluck('PartnerName', 'PartnerID')->toArray();
        });
    }

    public static function listWarehouses(): array
    {
        return Cache::remember('Partner::listWarehouses',now()->addDay(),function () {
            return self::where('ProductWarehouse',1)->pluck('PartnerName', 'PartnerID')->toArray();
        });
    }

    public function getPartnerName():?string {
        if ($this->PhysicalPerson == 0) {
            $partner = $this->PartnerTitle.' '.$this->PartnerName;
        } else {
            $partner = $this->PhysicalPersonFirstName.' '.$this->PartnerName;
        }

        return $partner;
    }

    public static function getAllBusinessTypes(): array {
        return self::select('PartnerTitle')
            ->where('PhysicalPerson', 0)
            ->whereNotNull('PartnerTitle')
            ->distinct()->get()->pluck('PartnerTitle', 'PartnerTitle')->toArray();
    }
}
