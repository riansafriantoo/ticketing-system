<?php

namespace App\Enums;

enum TicketCategory: string
{
    case Hardware    = 'hardware';
    case Software    = 'software';
    case Network     = 'network';
    case Access      = 'access';
    case Email       = 'email';
    case Other       = 'other';

    public function label(): string
    {
        return match($this) {
            self::Hardware => 'Hardware',
            self::Software => 'Software',
            self::Network  => 'Network',
            self::Access   => 'Access / Permissions',
            self::Email    => 'Email',
            self::Other    => 'Other',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}