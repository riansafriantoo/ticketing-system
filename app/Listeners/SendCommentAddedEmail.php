<?php

namespace App\Listeners;

use App\Events\CommentAdded;
use App\Listeners\Concerns\PreventsDuplicateNotification;
use App\Listeners\Concerns\ReliableMailSender;
use App\Mail\CommentAddedMail;
use App\Services\NotificationRecipientResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCommentAddedEmail implements ShouldQueue
{
    use PreventsDuplicateNotification;
    use ReliableMailSender;

    public string $queue = 'emails';
    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        private readonly NotificationRecipientResolver $resolver
    ) {}

    public function handle(CommentAdded $event): void
    {
        $recipients = $this->resolver->forCommentAdded($event->ticket, $event->comment);

        if ($recipients->isEmpty()) {
            return;
        }

        $fingerprint = $this->fingerprintCommentAdded(
            $event->ticket->id,
            $event->comment->id,
        );

        if ($this->alreadySent($fingerprint, 'comment_added', $recipients, $event->ticket->id)) {
            return;
        }

        $this->sendToEach(
            recipients: $recipients,
            mailable:   new CommentAddedMail($event->ticket, $event->comment, $recipients),
            eventType:  'CommentAdded',
            logContext:  [
                'ticket_id'  => $event->ticket->id,
                'comment_id' => $event->comment->id,
            ],
        );
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