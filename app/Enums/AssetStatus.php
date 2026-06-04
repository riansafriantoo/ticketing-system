<?php

namespace App\Enums;

enum AssetStatus: string
{
    case Active      = 'active';
    case InStorage   = 'in_storage';
    case UnderRepair = 'under_repair';
    case Retired     = 'retired';
    case Disposed    = 'disposed';

    public function label(): string
    {
        return match($this) {
            self::Active      => 'Active',
            self::InStorage   => 'In Storage',
            self::UnderRepair => 'Under Repair',
            self::Retired     => 'Retired',
            self::Disposed    => 'Disposed',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Active      => 'green',
            self::InStorage   => 'blue',
            self::UnderRepair => 'amber',
            self::Retired     => 'gray',
            self::Disposed    => 'red',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Active      => 'bg-green-100 text-green-800',
            self::InStorage   => 'bg-blue-100 text-blue-800',
            self::UnderRepair => 'bg-amber-100 text-amber-800',
            self::Retired     => 'bg-gray-100 text-gray-600',
            self::Disposed    => 'bg-red-100 text-red-700',
        };
    }

    public function isAvailableForAssignment(): bool
    {
        return $this === self::Active || $this === self::InStorage;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}