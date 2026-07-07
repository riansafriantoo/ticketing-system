<?php

namespace App\Mail;

use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CommentAddedMail extends Mailable 
{
    use SerializesModels;
    
    public function __construct(
        public readonly Ticket $ticket,
        public readonly Comment $comment,
        public readonly Collection $recipients,   // ← was missing
    ) {}

    public function build(): self
    {
        return $this
            ->subject("Ticketing System - New reply on {$this->ticket->ticketNumber()}")
            //->cc('helpdesk.support@fc-network.com')
            ->markdown('emails.comment-added', [
                'ticket'  => $this->ticket,
                'comment' => $this->comment,
            ]);
    }
}