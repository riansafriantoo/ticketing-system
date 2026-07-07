<?php

namespace App\Mail;

use App\Enums\TicketStatusNew;
use App\Models\Ticket;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class TicketStatusChangedMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly Ticket $ticket,
        public readonly TicketStatusNew $oldStatus,
        public readonly TicketStatusNew $newStatus,
        public readonly Collection $recipients,   // ← was missing
    ) {}

    public function build(): self
    {
        return $this
            ->subject("Ticketing System - Status updated on [{$this->ticket->ticketNumber()}]")
            //->cc('helpdesk.support@fc-network.com')
            ->markdown('emails.ticket-status-changed', [
                'ticket'    => $this->ticket,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
            ]);
    }
}