<?php

namespace App\Models\Jumis;

use App\Jumis;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreDocStatus extends Jumis
{
    protected $table        = 'StoreDocStatus';
    protected $primaryKey   = 'StatusID';

    public function documents(): HasMany
    {
        return $this->hasMany(StoreDoc::class,'DocStatus','StatusID');
    }

}
