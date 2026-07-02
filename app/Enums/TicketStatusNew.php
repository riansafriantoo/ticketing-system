<?php

namespace App\Enums;

enum TicketStatusNew: string
{
    case Open       = 'open';
    case InProgress = 'in_progress';
    case OnHold     = 'on_hold';
    case Closed     = 'closed';

    public function label(): string
    {
        return match($this) {
            self::Open       => 'Open',
            self::InProgress => 'In Progress',
            self::OnHold     => 'On Hold',
            self::Closed     => 'Closed',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Open       => 'blue',
            self::InProgress => 'amber',
            self::OnHold     => 'gray',
            self::Closed     => 'slate',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Closed]);
    }

    /** Allowed next states from current */
    public function transitions(): array
    {
        return match($this) {
            self::Open       => [self::InProgress, self::Closed, self::OnHold],
            self::InProgress => [self::Open, self::Closed, self::OnHold],
            self::OnHold     => [self::Open, self::InProgress, self::Closed],
            self::Closed     => [self::InProgress, self::Open],
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}