<?php

namespace App\Listeners;

use App\Events\TicketStatusChanged;
use App\Mail\TicketStatusChangedMail;
use App\Services\NotificationRecipientResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTicketStatusChangedEmail implements ShouldQueue
{
    public string $queue = 'emails';
    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        private readonly NotificationRecipientResolver $resolver
    ) {}

    public function handle(TicketStatusChanged $event): void
    {
        $recipients = $this->resolver->forStatusChanged($event->ticket, $event->changedBy);

        foreach ($recipients as $user) {
            try {
                Mail::to($user->email)->send(new TicketStatusChangedMail(
                    $event->ticket,
                    $event->oldStatus,
                    $event->newStatus,
                ));
            } catch (\Throwable $e) {
                Log::error('TicketStatusChanged email failed', [
                    'ticket_id' => $event->ticket->id,
                    'user_id'   => $user->id,
                    'error'     => $e->getMessage(),
                ]);
            }
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