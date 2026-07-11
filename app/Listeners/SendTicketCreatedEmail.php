<?php

namespace App\Listeners;

use App\Events\TicketCreated;
use App\Listeners\Concerns\PreventsDuplicateNotification;
use App\Listeners\Concerns\ReliableMailSender;
use App\Mail\TicketCreatedMail;
use App\Services\NotificationRecipientResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;


class SendTicketCreatedEmail implements ShouldQueue
{
    use PreventsDuplicateNotification;
    use ReliableMailSender;  

    public string $queue = 'emails';
    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        private readonly NotificationRecipientResolver $resolver
    ) {}

    public function handle(TicketCreated $event): void
    {
        $recipients = $this->resolver->forTicketCreated($event->ticket, $event->createdBy);

        if ($recipients->isEmpty()) {
            return;
        }

        $fingerprint = $this->fingerprintCreated($event->ticket->id);

        if ($this->alreadySent($fingerprint, 'ticket_created', $recipients, $event->ticket->id)) {
            return;
        }

        $this->sendToEach(
            recipients: $recipients,
            mailable:   new TicketCreatedMail($event->ticket, $recipients),
            eventType:  'TicketCreated',
            logContext:  ['ticket_id' => $event->ticket->id],
        );
    }

    public function failed(TicketCreated $event, \Throwable $exception): void
    {
        Log::critical('SendTicketCreatedEmail permanently failed', [
            'ticket_id' => $event->ticket->id,
            'error'     => $exception->getMessage(),
        ]);
    }
}