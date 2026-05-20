<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Activity;
use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketCreated;
use App\Notifications\TicketStatusChanged;
use App\Notifications\TicketAssigned;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketService
{
    public function __construct(
        private readonly NotificationService $notifier
    ) {}

    // ─── Create ───────────────────────────────────────────────────────────────

    public function create(array $data, User $requester): Ticket
    {
        return DB::transaction(function () use ($data, $requester) {
            $ticket = Ticket::create([
                ...$data,
                'requester_id' => $requester->id,
            ]);

            $this->logActivity($ticket, $requester, 'created');

            // Store attachments
            if (!empty($data['attachments'])) {
                $this->storeAttachments($ticket, $data['attachments'], $requester);
            }

            $this->notifier->ticketCreated($ticket);

            return $ticket->fresh(['requester', 'assignee']);
        });
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function update(Ticket $ticket, array $data, User $actor): Ticket
    {
        return DB::transaction(function () use ($ticket, $data, $actor) {
            $changes = [];

            if (isset($data['priority']) && $data['priority'] !== $ticket->priority->value) {
                $changes[] = ['action' => 'priority_changed', 'meta' => [
                    'from' => $ticket->priority->value,
                    'to'   => $data['priority'],
                ]];
            }

            $ticket->update($data);

            foreach ($changes as $change) {
                $this->logActivity($ticket, $actor, $change['action'], $change['meta']);
            }

            return $ticket->fresh();
        });
    }

    // ─── Status transition ────────────────────────────────────────────────────

    public function transition(Ticket $ticket, TicketStatus $newStatus, User $actor): Ticket
    {
        if (!$ticket->canTransitionTo($newStatus)) {
            throw new \DomainException(
                "Cannot transition from {$ticket->status->value} to {$newStatus->value}"
            );
        }

        return DB::transaction(function () use ($ticket, $newStatus, $actor) {
            $oldStatus = $ticket->status;

            $updates = ['status' => $newStatus];
            if ($newStatus === TicketStatus::Resolved) $updates['resolved_at'] = now();
            if ($newStatus === TicketStatus::Closed)   $updates['closed_at']   = now();

            $ticket->update($updates);

            $this->logActivity($ticket, $actor, 'status_changed', [
                'from' => $oldStatus->value,
                'to'   => $newStatus->value,
            ]);

            $this->notifier->statusChanged($ticket, $oldStatus);

            return $ticket->fresh();
        });
    }

    // ─── Assign ───────────────────────────────────────────────────────────────

    public function assign(Ticket $ticket, ?User $assignee, User $actor): Ticket
    {
        return DB::transaction(function () use ($ticket, $assignee, $actor) {
            $ticket->update(['assignee_id' => $assignee?->id]);

            $this->logActivity($ticket, $actor, 'assigned', [
                'to' => $assignee?->name ?? 'Unassigned',
            ]);

            if ($assignee) {
                $this->notifier->ticketAssigned($ticket, $assignee);
            }

            return $ticket->fresh(['assignee']);
        });
    }

    // ─── Attachments ──────────────────────────────────────────────────────────

    private function storeAttachments(Ticket $ticket, array $files, User $uploader): void
    {
        foreach ($files as $file) {
            if (!($file instanceof UploadedFile)) continue;
            if (!$file->isValid()) continue;

            $path = $file->store("tickets/{$ticket->id}/attachments", 'public');

            Attachment::create([
                'ticket_id'     => $ticket->id,
                'user_id'       => $uploader->id,
                'original_name' => $file->getClientOriginalName(),
                'stored_name'   => $path,
                'mime_type'     => $file->getMimeType(),
                'size'          => $file->getSize(),
                'disk'          => 'public',
            ]);
        }
    }

    // ─── Activity log ─────────────────────────────────────────────────────────

    private function logActivity(Ticket $ticket, User $actor, string $action, array $meta = []): void
    {
        try {
            Activity::create([
                'ticket_id'  => $ticket->id,
                'user_id'    => $actor->id,
                'action'     => $action,
                'meta'       => $meta,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error("Activity log failed: {$e->getMessage()}");
        }
    }

    // ─── Metrics (for dashboard) ──────────────────────────────────────────────

    public function metrics(): array
    {
        return [
            'total_open'     => Ticket::whereNotIn('status', [TicketStatus::Resolved, TicketStatus::Closed])->count(),
            'overdue'        => Ticket::overdue()->count(),
            'resolved_today' => Ticket::whereDate('resolved_at', today())->count(),
            'avg_resolution' => $this->avgResolutionHours(),
        ];
    }

    private function avgResolutionHours(): float
    {
        $avg = Ticket::whereNotNull('resolved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg')
            ->value('avg');

        return round((float) $avg, 1);
    }
}