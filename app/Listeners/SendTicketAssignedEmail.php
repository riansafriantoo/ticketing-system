<?php

namespace App\Listeners;

use App\Events\TicketAssigned;
use App\Mail\TicketAssignedMail;
use App\Services\NotificationRecipientResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTicketAssignedEmail implements ShouldQueue
{
    public string $queue = 'emails';
    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        private readonly NotificationRecipientResolver $resolver
    ) {}

    public function handle(TicketAssigned $event): void
    {
        // Unassigning a ticket (newAssignee === null) doesn't warrant
        // an email to anyone — there's no one to notify "you have work"
        // and the requester doesn't need to know it's back in the queue.
        if ($event->newAssignee === null) {
            return;
        }

        $recipients = $this->resolver->forTicketAssigned(
            $event->ticket,
            $event->newAssignee,
            $event->assignedBy,
        );

        foreach ($recipients as $user) {
            try {
                Mail::to($user->email)->send(new TicketAssignedMail(
                    $event->ticket,
                    $event->newAssignee,
                ));
            } catch (\Throwable $e) {
                Log::error('TicketAssigned email failed', [
                    'ticket_id' => $event->ticket->id,
                    'user_id'   => $user->id,
                    'error'     => $e->getMessage(),
                ]);
            }
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