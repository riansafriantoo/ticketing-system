<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Open       = 'open';
    case InProgress = 'in_progress';
    case OnHold     = 'on_hold';
    case Resolved   = 'resolved';
    case Closed     = 'closed';

    public function label(): string
    {
        return match($this) {
            self::Open       => 'Open',
            self::InProgress => 'In Progress',
            self::OnHold     => 'On Hold',
            self::Resolved   => 'Resolved',
            self::Closed     => 'Closed',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Open       => 'blue',
            self::InProgress => 'amber',
            self::OnHold     => 'gray',
            self::Resolved   => 'green',
            self::Closed     => 'slate',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Resolved, self::Closed]);
    }

    /** Allowed next states from current */
    public function transitions(): array
    {
        return match($this) {
            self::Open       => [self::InProgress, self::OnHold, self::Closed],
            self::InProgress => [self::OnHold, self::Resolved, self::Closed],
            self::OnHold     => [self::InProgress, self::Closed],
            self::Resolved   => [self::Closed, self::Open],
            self::Closed     => [self::Open],
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}