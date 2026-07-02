<?php

namespace App\Events;

use App\Enums\TicketStatus;
use App\Enums\TicketStatusNew;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired whenever a ticket transitions between statuses
 * (Open → In Progress, In Progress → On Hold, → Resolved, → Closed, etc).
 *
 * Carries both the old and new status so the listener/mail can render
 * a meaningful "X → Y" message without re-deriving it from the ticket's
 * current state (which has already moved on by the time the queued
 * listener runs).
 */
class TicketStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Ticket $ticket,
        public readonly TicketStatusNew $oldStatus,
        public readonly TicketStatusNew $newStatus,
        public readonly User $changedBy,
    ) {}
}