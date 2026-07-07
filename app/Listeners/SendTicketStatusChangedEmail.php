<?php

namespace App\Listeners;

use App\Events\TicketStatusChanged;
use App\Listeners\Concerns\PreventsDuplicateNotification;
use App\Mail\TicketStatusChangedMail;
use App\Services\NotificationRecipientResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTicketStatusChangedEmail implements ShouldQueue
{
    use PreventsDuplicateNotification;

    public string $queue = 'emails';
    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        private readonly NotificationRecipientResolver $resolver
    ) {}

    public function handle(TicketStatusChanged $event): void
    {
        $recipients = $this->resolver->forStatusChanged($event->ticket, $event->changedBy);

        if ($recipients->isEmpty()) {
            return;
        }

        $fingerprint = $this->fingerprintStatusChanged(
            $event->ticket->id,
            $event->newStatus->value,
        );

        if ($this->alreadySent($fingerprint, 'ticket_status_changed', $recipients, $event->ticket->id)) {
            return;
        }

        $toList = $recipients
            ->map(fn ($u) => ['email' => $u->email, 'name' => $u->name])
            ->toArray();

        try {
            Mail::to($toList)->send(
                new TicketStatusChangedMail(
                    $event->ticket,
                    $event->oldStatus,
                    $event->newStatus,
                    $recipients,
                )
            );
        } catch (\Throwable $e) {
            Log::error('TicketStatusChanged email failed', [
                'ticket_id'  => $event->ticket->id,
                'recipients' => $recipients->pluck('email')->toArray(),
                'error'      => $e->getMessage(),
            ]);
        }
    }

    public function failed(TicketStatusChanged $event, \Throwable $exception): void
    {
        Log::critical('SendTicketStatusChangedEmail permanently failed', [
            'ticket_id' => $event->ticket->id,
            'error'     => $exception->getMessage(),
        ]);
    }
}