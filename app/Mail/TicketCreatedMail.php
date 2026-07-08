<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class TicketCreatedMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly Ticket $ticket,
        public readonly Collection $recipients,   // ← was missing
    ) {}

    public function build(): self
    {
        return $this
            ->subject("Ticketing System - New ticket has been created with this ticket number : {{$this->ticket->ticketNumber()}}")
            //->cc('helpdesk.support@fc-network.com')
            ->markdown('emails.ticket-created', [
                'ticket' => $this->ticket,
            ]);
    }
}