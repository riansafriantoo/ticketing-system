<?php

namespace App\Enums;

enum TicketPriorityRequester: string
{
    case Low      = '4';
    case Medium   = '3';

    public function label(): string
    {
        return match($this) {
            self::Low      => '4 (Low)',
            self::Medium   => '3 (Medium)',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Low      => 'green',
            self::Medium   => 'blue',
        };
    }

    /** SLA response time in hours */
    public function slaHours(): int
    {
        return match($this) {
            self::Low      => (int) config('it-ticketing.sla.low', 72),
            self::Medium   => (int) config('it-ticketing.sla.medium', 24),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}