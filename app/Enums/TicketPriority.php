<?php

namespace App\Enums;

enum TicketPriority: string
{
    case Low      = 'low';
    case Medium   = 'medium';
    case High     = 'high';
    case Critical = 'critical';

    public function label(): string
    {
        return match($this) {
            self::Low      => 'Low',
            self::Medium   => 'Medium',
            self::High     => 'High',
            self::Critical => 'Critical',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Low      => 'green',
            self::Medium   => 'blue',
            self::High     => 'amber',
            self::Critical => 'red',
        };
    }

    /** SLA response time in hours */
    public function slaHours(): int
    {
        return match($this) {
            self::Low      => (int) config('it-ticketing.sla.low', 72),
            self::Medium   => (int) config('it-ticketing.sla.medium', 24),
            self::High     => (int) config('it-ticketing.sla.high', 8),
            self::Critical => (int) config('it-ticketing.sla.critical', 4),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}