<?php

namespace App\Listeners;

use App\Events\TicketCreated;
use App\Listeners\Concerns\PreventsDuplicateNotification;
use App\Mail\TicketCreatedMail;
use App\Services\NotificationRecipientResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTicketCreatedEmail implements ShouldQueue
{
    use PreventsDuplicateNotification;

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

        $toList = $recipients
            ->map(fn ($u) => ['email' => $u->email, 'name' => $u->name])
            ->toArray();

        try {
            Mail::to($toList)->send(
                new TicketCreatedMail($event->ticket, $recipients)
            );
        } catch (\Throwable $e) {
            Log::error('TicketCreated email failed', [
                'ticket_id'  => $event->ticket->id,
                'recipients' => $recipients->pluck('email')->toArray(),
                'error'      => $e->getMessage(),
            ]);
        }
    }

    public function failed(TicketCreated $event, \Throwable $exception): void
    {
        Log::critical('SendTicketCreatedEmail permanently failed', [
            'ticket_id' => $event->ticket->id,
            'error'     => $exception->getMessage(),
        ]);
    }
}