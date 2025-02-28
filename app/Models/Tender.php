<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tender extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected static function booted()
    {
        static::deleting(function ($resource) {
            foreach ($resource->docLines()->get() as $item) {
                $item->delete();
            }
        });

        static::restoring(function($resource) {
            foreach ($resource->docLines()->withTrashed()->get() as $item) {
                $item->restore();
            }
        });
    }

    public function docLines()
    {
        return $this->hasMany(DocLine::class);
    }
}
