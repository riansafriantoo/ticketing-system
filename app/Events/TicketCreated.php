<?php

namespace App\Events;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired the moment a ticket is successfully persisted.
 *
 * This event carries no knowledge of who should be emailed or how —
 * that responsibility belongs entirely to the listener and the
 * NotificationRecipientResolver. Keeping the event "dumb" (just data)
 * means the same event could later power a Slack notification, an
 * audit log entry, or a webhook without touching this class.
 */
class TicketCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Ticket $ticket,
        public readonly User $createdBy,
    ) {}
}