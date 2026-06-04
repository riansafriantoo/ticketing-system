<?php

namespace App\Enums;

enum TicketCaseType: string
{
    case Hardware    = 'hardware';
    case Software    = 'software';

    public function label(): string
    {
        return match($this) {
            self::Hardware => 'Hardware',
            self::Software => 'Software',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}