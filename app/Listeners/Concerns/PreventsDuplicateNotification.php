<?php

namespace App\Listeners\Concerns;

use App\Models\NotificationLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

trait PreventsDuplicateNotification
{
    protected function alreadySent(
        string     $fingerprint,
        string     $eventType,
        Collection $recipients,
        ?int       $ticketId = null,
    ): bool {
        try {
            NotificationLog::create([
                'fingerprint'      => $fingerprint,
                'event_type'       => $eventType,
                'ticket_id'        => $ticketId,
                'recipient_emails' => $recipients->pluck('email')->toArray(),
                'sent_at'          => now(),
            ]);

            return false;

        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            Log::warning('Duplicate notification suppressed', [
                'fingerprint' => $fingerprint,
                'event_type'  => $eventType,
                'ticket_id'   => $ticketId,
            ]);

            return true;
        }
    }

    protected function fingerprintCreated(int $ticketId): string
    {
        return hash('sha256', "ticket_created:{$ticketId}");
    }

    protected function fingerprintStatusChanged(int $ticketId, string $newStatus): string
    {
        $minute = now()->format('Y-m-d H:i');
        return hash('sha256', "ticket_status_changed:{$ticketId}:{$newStatus}:{$minute}");
    }

    protected function fingerprintAssigned(int $ticketId, ?int $assigneeId): string
    {
        $minute = now()->format('Y-m-d H:i');
        return hash('sha256', "ticket_assigned:{$ticketId}:{$assigneeId}:{$minute}");
    }

    protected function fingerprintCommentAdded(int $ticketId, int $commentId): string
    {
        return hash('sha256', "comment_added:{$ticketId}:{$commentId}");
    }
}