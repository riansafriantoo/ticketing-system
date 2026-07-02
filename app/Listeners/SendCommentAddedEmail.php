<?php

namespace App\Listeners;

use App\Events\CommentAdded;
use App\Mail\CommentAddedMail;
use App\Services\NotificationRecipientResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCommentAddedEmail implements ShouldQueue
{
    public string $queue = 'emails';
    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        private readonly NotificationRecipientResolver $resolver
    ) {}

    public function handle(CommentAdded $event): void
    {
        $recipients = $this->resolver->forCommentAdded($event->ticket, $event->comment);

        foreach ($recipients as $user) {
            try {
                Mail::to($user->email)->send(new CommentAddedMail(
                    $event->ticket,
                    $event->comment,
                ));
            } catch (\Throwable $e) {
                Log::error('CommentAdded email failed', [
                    'ticket_id'  => $event->ticket->id,
                    'comment_id' => $event->comment->id,
                    'user_id'    => $user->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }
    }

    public function failed(CommentAdded $event, \Throwable $exception): void
    {
        Log::critical('SendCommentAddedEmail permanently failed', [
            'ticket_id'  => $event->ticket->id,
            'comment_id' => $event->comment->id,
            'error'      => $exception->getMessage(),
        ]);
    }
}