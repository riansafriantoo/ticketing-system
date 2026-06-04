<?php

namespace App\Enums;

enum TicketCategory: string
{
    case Network     = 'network';
    case Firewall    = 'firewall';
    case Server      = 'server';
    case VoIP        = 'voip';
    case Multimedia  = 'multimedia';
    case Peripheral  = 'peripheral';
    case EndUser     = 'end_user';


    public function label(): string
    {
        return match($this) {
            self::Network => 'Network',
            self::Firewall => 'Firewall',
            self::Server => 'Server',
            self::VoIP => 'VoIP',
            self::Multimedia => 'Multimedia',
            self::Peripheral => 'Peripheral',
            self::EndUser => 'End User',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}