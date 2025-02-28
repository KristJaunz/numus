<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocLine extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function tender()
    {
        return $this->belongsTo(Tender::class)->withTrashed();
    }
}
