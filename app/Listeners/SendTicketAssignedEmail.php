<?php

namespace App\Listeners;

use App\Events\TicketAssigned;
use App\Listeners\Concerns\PreventsDuplicateNotification;
use App\Mail\TicketAssignedMail;
use App\Services\NotificationRecipientResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTicketAssignedEmail implements ShouldQueue
{
    use PreventsDuplicateNotification;

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

        $toList = $recipients
            ->map(fn ($u) => ['email' => $u->email, 'name' => $u->name])
            ->toArray();

        try {
            Mail::to($toList)->send(
                new TicketAssignedMail(
                    $event->ticket,
                    $event->newAssignee,
                    $recipients,
                )
            );
        } catch (\Throwable $e) {
            Log::error('TicketAssigned email failed', [
                'ticket_id'  => $event->ticket->id,
                'recipients' => $recipients->pluck('email')->toArray(),
                'error'      => $e->getMessage(),
            ]);
        }
    }

    public function failed(TicketAssigned $event, \Throwable $exception): void
    {
        Log::critical('SendTicketAssignedEmail permanently failed', [
            'ticket_id' => $event->ticket->id,
            'error'     => $exception->getMessage(),
        ]);
    }
}