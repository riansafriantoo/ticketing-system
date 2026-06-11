<?php

namespace App\Enums;

enum Department: string
{
    case IPI          = 'IPI';
    case PERSERO      = 'PERSERO';

    public function label(): string
    {
        return match($this) {
            self::IPI          => 'IPI',
            self::PERSERO      => 'PERSERO',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::IPI          => 'blue',
            self::PERSERO      => 'green',
        };
    }


    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}