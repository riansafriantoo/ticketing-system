<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class TicketAssignedMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly Ticket $ticket,
        public readonly ?User $newAssignee,
        public readonly Collection $recipients,   // ← was missing
    ) {}

    public function build(): self
    {
        return $this
            ->subject("Ticketing System - Ticket {$this->ticket->ticketNumber()} assigned to: {$this->ticket->subject}")
            //->cc('helpdesk.support@fc-network.com')
            ->markdown('emails.ticket-assigned', [
                'ticket'      => $this->ticket,
                'newAssignee' => $this->newAssignee,
            ]);
    }
}