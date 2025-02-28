<?php

namespace App\Models\Jumis\Structures;


enum PartnerPersonType: int
{
    CASE LEGAL_PERSON = 0;
    CASE PHYSICAL_PERSON = 1;
    CASE EMPLOYEE = 2;//Employee


    public function label(): string {
        return match($this) {
            self::LEGAL_PERSON => __('Legal Person'),
            self::PHYSICAL_PERSON => __('Physical Person'),
            self::EMPLOYEE => __('Employee'),
        };
    }

    public static function list(): array {
        return [
            self::LEGAL_PERSON->value => __('Legal Person'),
            self::PHYSICAL_PERSON->value => __('Physical Person'),
            self::EMPLOYEE->value => __('Employee'),

        ];
    }

    public static function name($type) {
        return self::list()[$type] ?? __('Unknown Partner Type');
    }
}
