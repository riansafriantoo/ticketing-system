<?php

namespace App\Enums;

enum AssetCategory: string
{
    case Laptop      = 'laptop';
    case Desktop     = 'desktop';
    case Monitor     = 'monitor';
    case Printer     = 'printer';
    case Network     = 'network';
    case Phone       = 'phone';
    case Tablet      = 'tablet';
    case Peripheral  = 'peripheral';
    case Server      = 'server';
    case Software    = 'software';
    case Furniture   = 'furniture';
    case Other       = 'other';

    public function label(): string
    {
        return match($this) {
            self::Laptop     => 'Laptop',
            self::Desktop    => 'Desktop',
            self::Monitor    => 'Monitor',
            self::Printer    => 'Printer',
            self::Network    => 'Network Equipment',
            self::Phone      => 'Phone',
            self::Tablet     => 'Tablet',
            self::Peripheral => 'Peripheral',
            self::Server     => 'Server',
            self::Software   => 'Software License',
            self::Furniture  => 'Furniture',
            self::Other      => 'Other',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Laptop     => '💻',
            self::Desktop    => '🖥️',
            self::Monitor    => '🖥️',
            self::Printer    => '🖨️',
            self::Network    => '🔌',
            self::Phone      => '📱',
            self::Tablet     => '📱',
            self::Peripheral => '🖱️',
            self::Server     => '🖧',
            self::Software   => '📀',
            self::Furniture  => '🪑',
            self::Other      => '📦',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}