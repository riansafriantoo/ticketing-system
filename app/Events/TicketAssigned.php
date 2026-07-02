<?php

namespace App\Events;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a ticket's assignee changes — including the first
 * assignment (previousAssignee will be null) and reassignment
 * (previousAssignee holds who it was taken from).
 *
 * Both the new assignee and the requester get notified — handled by
 * NotificationRecipientResolver, not duplicated here.
 */
class TicketAssigned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Ticket $ticket,
        public readonly ?User $previousAssignee,
        public readonly ?User $newAssignee,
        public readonly User $assignedBy,
    ) {}
}