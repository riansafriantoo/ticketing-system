<?php

namespace App\Mail;

use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CommentAddedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public readonly Ticket $ticket,
        public readonly Comment $comment,
    ) {}

    public function build(): self
    {
        $prefix = $this->comment->is_internal ? '[Internal] ' : '';

        return $this
            ->subject("[{$this->ticket->ticketNumber()}] {$prefix}New reply: {$this->ticket->subject}")
            ->markdown('emails.comment-added', [
                'ticket'  => $this->ticket,
                'comment' => $this->comment,
            ]);
    }
}