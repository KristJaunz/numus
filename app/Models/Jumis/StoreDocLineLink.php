<?php

namespace App\Models\Jumis;


use App\Jumis;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreDocLineLink extends Jumis
{
    protected $table = 'StoreDocLineLink';
    protected $primaryKey = 'LinkID';

    public function fromLine(): BelongsTo
    {
        return $this->belongsTo(StoreDocLine::class,'FirstLineID','LineID');
    }


    public function toLine(): BelongsTo
    {
        return $this->belongsTo(StoreDocLine::class,'ToID','LineID');
    }

}
