<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $guarded = [];


    public function tender()
    {
        return $this->belongsTo(Tender::class)->withTrashed();
    }

    public static function write(?Tender $tender, $message)
    {
        try
        {
            $log = new Log();
            $log->tender_id = $tender->id;
            $log->message = $message;
            $log->save();
        }
        catch (\Exception $e)
        {

        }

        \Illuminate\Support\Facades\Log::channel('numus')->error($message,$tender->toArray());
    }
}

