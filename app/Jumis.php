<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Actionable;

class Jumis extends Model
{
   // use Actionable;



    protected $connection = 'sqlsrv';
    protected $guarded = [];
    public $timestamps = false;
}
