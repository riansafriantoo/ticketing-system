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

    public function create(array $data, array $attachments, User $requester): Ticket
    {
        return DB::transaction(function () use ($data, $attachments, $requester) {
 
            $ticketData = array_diff_key($data, array_flip(['attachments', 'files']));
            
 
            $ticket = Ticket::create([
                ...$ticketData,
                'requester_id' => $requester->id,
            ]);
 
            $this->logActivity($ticket, $requester, 'created');
 
            if (!empty($attachments)) {
                $this->storeAttachments($ticket, $attachments, $requester);
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
            if ($newStatus === TicketStatus::Closed) {
                $updates['resolved_at'] = now();
                $updates['closed_at'] = now();
            }

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
 
            if (!($file instanceof UploadedFile)) {
                Log::warning("Skipped non-UploadedFile attachment for ticket #{$ticket->id}", [
                    'type'  => gettype($file),
                    'value' => is_string($file) ? $file : '(non-string)',
                ]);
                continue;
            }
 
            // ── Guard 2: upload must have succeeded (no PHP upload error) ─────
            if (!$file->isValid()) {
                Log::warning("Skipped invalid upload for ticket #{$ticket->id}", [
                    'error' => $file->getError(),
                    'name'  => $file->getClientOriginalName(),
                ]);
                continue;
            }
 
            // ── Guard 3: enforce max size (belt-and-suspenders) ───────────────
            $maxBytes = config('servicedesk.uploads.max_size_kb', 10240) * 1024;
            if ($file->getSize() > $maxBytes) {
                Log::warning("Skipped oversized attachment for ticket #{$ticket->id}", [
                    'size' => $file->getSize(),
                    'name' => $file->getClientOriginalName(),
                ]);
                continue;
            }
 
            try {
                $storedPath = $file->store(
                    "tickets/{$ticket->id}/attachments",
                    'public'
                );
 
                if (!$storedPath) {
                    Log::error("Storage::store() returned false for ticket #{$ticket->id}");
                    continue;
                }
 
                // Insert the Attachment record
                Attachment::create([
                    'ticket_id'     => $ticket->id,
                    'user_id'       => $uploader->id,
                    'original_name' => $file->getClientOriginalName(),
                    'stored_name'   => $storedPath,
                    'mime_type'     => $file->getMimeType() ?? $file->getClientMimeType(),
                    'size'          => $file->getSize(),
                    'disk'          => 'public',
                ]);
 
                Log::info("Attachment stored for ticket #{$ticket->id}: {$storedPath}");
 
            } catch (\Throwable $e) {
                // Log but don't abort the transaction — ticket still gets created
                Log::error("Attachment storage failed for ticket #{$ticket->id}: {$e->getMessage()}", [
                    'file'  => $file->getClientOriginalName(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
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
            'in_progress'     => Ticket::where('status', TicketStatus::InProgress)->count(),
            'total_tickets'  => Ticket::count(),
            'overdue'        => Ticket::overdue()->count(),
            'resolved_today' => Ticket::whereIn('status', [TicketStatus::Closed])->count(),
            'avg_resolution' => $this->avgResolutionHours(),
        ];
    }

    public function metricsRequester(User $user): array
    {
        return [
            'total_open'     => Ticket::join('users', 'tickets.requester_id', '=', 'users.id')->where('users.department', $user->department)->where('status', TicketStatus::InProgress)->count(),
            'total_tickets'  => Ticket::join('users', 'tickets.requester_id', '=', 'users.id')->where('users.department', $user->department)->count(),
            'in_progress'    => Ticket::join('users', 'tickets.requester_id', '=', 'users.id')->where('users.department', $user->department)->whereIn('status', [TicketStatus::InProgress])->count(),
            'overdue'        => Ticket::join('users', 'tickets.requester_id', '=', 'users.id')->where('users.department', $user->department)->overdue()->count(),
            'resolved'       => Ticket::join('users', 'tickets.requester_id', '=', 'users.id')->where('users.department', $user->department)->whereIn('status', [TicketStatus::Closed])->count(),
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