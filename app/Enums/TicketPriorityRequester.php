<?php

namespace App\Enums;

enum TicketPriorityRequester: string
{
    case Medium   = '3';
    case Low      = '4';

    public function label(): string
    {
        return match($this) {
            self::Medium   => '3 (Medium)',
            self::Low      => '4 (Low)',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Medium   => 'blue',
            self::Low      => 'green',
        };
    }

    /** SLA response time in hours */
    public function slaHours(): int
    {
        return match($this) {
            self::Medium   => (int) config('it-ticketing.sla.medium', 24),
            self::Low      => (int) config('it-ticketing.sla.low', 72),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}