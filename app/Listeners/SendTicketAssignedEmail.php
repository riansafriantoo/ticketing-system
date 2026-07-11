<?php

namespace App\Listeners;

use App\Events\TicketAssigned;
use App\Listeners\Concerns\PreventsDuplicateNotification;
use App\Listeners\Concerns\ReliableMailSender;
use App\Mail\TicketAssignedMail;
use App\Services\NotificationRecipientResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTicketAssignedEmail implements ShouldQueue
{
    use PreventsDuplicateNotification;
    use ReliableMailSender;

    public string $queue = 'emails';
    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        private readonly NotificationRecipientResolver $resolver
    ) {}

    public function handle(TicketAssigned $event): void
    {
        if ($event->newAssignee === null) {
            return;
        }

        $recipients = $this->resolver->forTicketAssigned(
            $event->ticket,
            $event->newAssignee,
            $event->assignedBy,
        );

        if ($recipients->isEmpty()) {
            return;
        }

        $fingerprint = $this->fingerprintAssigned(
            $event->ticket->id,
            $event->newAssignee->id,
        );

        if ($this->alreadySent($fingerprint, 'ticket_assigned', $recipients, $event->ticket->id)) {
            return;
        }

        $this->sendToEach(
            recipients: $recipients,
            mailable:   new TicketAssignedMail($event->ticket, $event->newAssignee, $recipients),
            eventType:  'TicketAssigned',
            logContext:  ['ticket_id' => $event->ticket->id],
        );

    }

    public function failed(TicketAssigned $event, \Throwable $exception): void
    {
        Log::critical('SendTicketAssignedEmail permanently failed', [
            'ticket_id' => $event->ticket->id,
            'error'     => $exception->getMessage(),
        ]);
    }
}