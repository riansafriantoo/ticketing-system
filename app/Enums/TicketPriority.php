<?php

namespace App\Enums;

enum TicketPriority: string
{
    case Critical = '1';
    case High     = '2';
    case Medium   = '3';
    case Low      = '4';

    public function label(): string
    {
        return match($this) {
            self::Critical => '1 (Critical)',
            self::High     => '2 (High)',
            self::Medium   => '3 (Medium)',
            self::Low      => '4 (Low)',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Critical => 'red',
            self::High     => 'amber',
            self::Medium   => 'blue',
            self::Low      => 'green',
        };
    }

    /** SLA response time in hours */
    public function slaHours(): int
    {
        return match($this) {
            self::Critical => (int) config('it-ticketing.sla.critical', 4),
            self::High     => (int) config('it-ticketing.sla.high', 8),
            self::Medium   => (int) config('it-ticketing.sla.medium', 24),
            self::Low      => (int) config('it-ticketing.sla.low', 72),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}