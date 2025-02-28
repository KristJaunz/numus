<?php

namespace App\Models\Jumis\Structures;

enum DocumentStatus: int
{
    CASE STARTED = 1;
    CASE ENTERED = 2;
    CASE CONFIRMED = 5;
    CASE ACCOUNTED = 6;

    public function label(): string {
        return match($this) {
            self::STARTED => __('Started'),
            self::ENTERED => __('Entered'),
            self::CONFIRMED => __('Confirmed'),
            self::ACCOUNTED => __('Accounted'),
        };
    }

    public static function list(): array {
        return [
            self::STARTED->value => __('Started'),
            self::ENTERED->value => __('Entered'),
            self::CONFIRMED->value => __('Confirmed'),
            self::ACCOUNTED->value => __('Accounted'),
        ];
    }

    public static function name($documentStatus) {
        return self::list()[$documentStatus] ?? __('Unknown Document Status');
    }
}
