<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketAssignedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public readonly Ticket $ticket,
        public readonly ?User $newAssignee,
    ) {}

    public function build(): self
    {
        return $this
            ->subject("[{$this->ticket->ticketNumber()}] Assigned: {$this->ticket->subject}")
            ->markdown('emails.ticket-assigned', [
                'ticket'      => $this->ticket,
                'newAssignee' => $this->newAssignee,
            ]);
    }
}