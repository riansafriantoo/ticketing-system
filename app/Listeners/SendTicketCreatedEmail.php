<?php

namespace App\Listeners;

use App\Events\TicketCreated;
use App\Mail\TicketCreatedMail;
use App\Services\NotificationRecipientResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Listens for TicketCreated and emails the requester + all admins.
 *
 * implements ShouldQueue — Laravel pushes this entire listener onto
 * the queue rather than running it inline during the HTTP request.
 * This means the person submitting a ticket gets an instant page
 * response; the email goes out a moment later via the queue worker.
 */
class SendTicketCreatedEmail implements ShouldQueue
{
    public string $queue = 'emails';
    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        private readonly NotificationRecipientResolver $resolver
    ) {}

    public function handle(TicketCreated $event): void
    {
        $recipients = $this->resolver->forTicketCreated($event->ticket, $event->createdBy);

        foreach ($recipients as $user) {
            try {
                Mail::to($user->email)->send(new TicketCreatedMail($event->ticket));
            } catch (\Throwable $e) {
                // Log and continue — one bad email address shouldn't
                // prevent the rest of the recipients from being notified.
                Log::error('TicketCreated email failed', [
                    'ticket_id' => $event->ticket->id,
                    'user_id'   => $user->id,
                    'error'     => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Called by Laravel after all retries are exhausted. Logged to
     * failed_jobs automatically, but we add a ticket-specific log line
     * too so it's easy to grep for "which tickets had notification
     * failures" without cross-referencing job IDs.
     */
    public function failed(TicketCreated $event, \Throwable $exception): void
    {
        Log::critical('SendTicketCreatedEmail permanently failed', [
            'ticket_id' => $event->ticket->id,
            'error'     => $exception->getMessage(),
        ]);
    }
}