<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Queue-level retry policy. If the mail server is briefly down,
     * Laravel retries before giving up — three attempts, 30s apart.
     */
    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(public readonly Ticket $ticket) {}

    public function build(): self
    {
        return $this
            ->subject("[{$this->ticket->ticketNumber()}] New ticket: {$this->ticket->subject}")
            ->markdown('emails.ticket-created', [
                'ticket' => $this->ticket,
            ]);
    }
}