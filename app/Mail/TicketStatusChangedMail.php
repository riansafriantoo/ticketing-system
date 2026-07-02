<?php

namespace App\Mail;

use App\Enums\TicketStatusNew;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketStatusChangedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public readonly Ticket $ticket,
        public readonly TicketStatusNew $oldStatus,
        public readonly TicketStatusNew $newStatus,
    ) {}

    public function build(): self
    {
        return $this
            ->subject("[{$this->ticket->ticketNumber()}] Status updated: {$this->newStatus->label()}")
            ->markdown('emails.ticket-status-changed', [
                'ticket'    => $this->ticket,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
            ]);
    }
}