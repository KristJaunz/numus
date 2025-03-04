<?php

namespace App\Components\DB;

use Illuminate\Database\QueryException;

class SqlServer
{

    public static function isRetryableError(QueryException $e): bool
    {
        $errorCodes = [
            '1205',     // Deadlock
            '10060',    // Timeout
            '10061',    // Network error
            '4060',     // Database not available
            '20000'     // Lock wait timeout exceeded (can vary)
        ];

        if (in_array($e->getCode(), $errorCodes)) {
            return true;
        }

        if (str_contains($e->getMessage(), 'deadlock') || str_contains($e->getMessage(), 'timeout')) {
            return true;
        }

        return false;
    }

}
