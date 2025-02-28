<?php

namespace App\Models\Jumis;


use App\Jumis;

class Country extends Jumis
{
    protected $table        = 'Country';
    protected $primaryKey   = 'CountryID';

    protected $casts = [
        'UpdateDate' => 'datetime',
        'CreateDate' => 'datetime',
    ];

}

