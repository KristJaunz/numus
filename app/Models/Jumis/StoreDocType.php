<?php

namespace App\Models\Jumis;


use App\Jumis;

class StoreDocType extends Jumis
{
    protected $table        = 'StoreDocType';
    protected $primaryKey   = 'StoreDocTypeID';

    public static function getDocumentTypes(): array
    {
        return self::all()->pluck('TypeName', 'StoreDocTypeID')->toArray();
    }

}
