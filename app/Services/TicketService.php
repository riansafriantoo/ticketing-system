<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Enums\TicketStatusNew;
use App\Events\CommentAdded;
use App\Events\TicketAssigned;
use App\Events\TicketCreated;
use App\Events\TicketStatusChanged;
use App\Models\Activity;
use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\TicketHold;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TicketService
{

    public function create(array $data, array $attachments, User $requester): Ticket
    {
        $ticket = DB::transaction(function () use ($data, $attachments, $requester) {
            $ticketData = array_diff_key($data, array_flip(['attachments', 'files']));

            $ticket = Ticket::create([
                ...$ticketData,
                'requester_id' => $requester->id,
            ]);

            $this->logActivity($ticket, $requester, 'created');

            if (!empty($attachments)) {
                $this->storeAttachments($ticket, $attachments, $requester);
            }

            return $ticket->fresh(['requester', 'assignee']);
        });

        Event::dispatch(new TicketCreated($ticket, $requester));

        return $ticket;
    }

    public function update(Ticket $ticket, array $data, User $actor): Ticket
    {
        return DB::transaction(function () use ($ticket, $data, $actor) {
            if (
                isset($data['priority']) &&
                $data['priority'] !== $ticket->priority->value
            ) {
                $this->logActivity($ticket, $actor, 'priority_changed', [
                    'from' => $ticket->priority->value,
                    'to'   => $data['priority'],
                ]);
            }

            $ticket->update($data);
            return $ticket->fresh();
        });
    }

    /**
     * Transition a ticket's status, with hold-time tracking and
     * email notification on every status change.
     */
    public function transition(Ticket $ticket, TicketStatusNew $newStatus, User $actor, ?string $holdReason = null): Ticket
    {
        if (!$ticket->canTransitionTo($newStatus)) {
            throw new \DomainException(
                "Cannot transition from {$ticket->status->value} to {$newStatus->value}"
            );
        }

        $oldStatus = $ticket->status;

        $ticket = DB::transaction(function () use ($ticket, $newStatus, $actor, $holdReason, $oldStatus) {
           
            $updates = ['status' => $newStatus];
            if ($newStatus === TicketStatusNew::OnHold) {
                $updates['hold_started_at'] = now();

                TicketHold::create([
                    'ticket_id' => $ticket->id,
                    'held_by'   => $actor->id,
                    'held_at'   => now(),
                    'reason'    => $holdReason,
                ]);
            }

            if ($newStatus === TicketStatusNew::Closed) {
                $updates['resolved_at'] = now();
                $updates['closed_at'] = now();
            }

            $ticket->update($updates);

            $this->logActivity($ticket, $actor, 'status_changed', [
                'from' => $oldStatus->value,
                'to'   => $newStatus->value,
            ]);

            return $ticket->fresh();
        });

        Event::dispatch(new TicketStatusChanged($ticket, $oldStatus, $newStatus, $actor));

        return $ticket;
    }


    public function assign(Ticket $ticket, ?User $assignee, User $actor): Ticket
    {
        $previousAssignee = $ticket->assignee;

        $ticket = DB::transaction(function () use ($ticket, $assignee, $actor) {
            $ticket->update(['assignee_id' => $assignee?->id]);

            $this->logActivity($ticket, $actor, 'assigned', [
                'to' => $assignee?->name ?? 'Unassigned',
            ]);

            return $ticket->fresh(['assignee']);
        });

        Event::dispatch(new TicketAssigned($ticket, $previousAssignee, $assignee, $actor));

        return $ticket;
    }

    public function addComment(Ticket $ticket, User $user, string $body, bool $isInternal = false): Comment
    {
        $comment = DB::transaction(function () use ($ticket, $user, $body, $isInternal) {
            $comment = $ticket->comments()->create([
                'user_id'     => $user->id,
                'body'        => $body,
                'is_internal' => $isInternal,
            ]);
 
            $this->logActivity($ticket, $user, 'comment_added', [
                'is_internal' => $isInternal,
            ]);
 
            return $comment;
        });
 
        Event::dispatch(new CommentAdded($ticket, $comment));
 
        return $comment;
    }

    public function metrics(User $user): array
    {
        return [
            'total_open'     => Ticket::whereNotIn('status', [TicketStatus::Resolved, TicketStatus::Closed])->count(),
            'total_tickets'  => Ticket::join('users', 'tickets.requester_id', '=', 'users.id')->where('users.department', $user->department)->count(),
            'in_progress'    => Ticket::join('users', 'tickets.requester_id', '=', 'users.id')->where('users.department', $user->department)->whereIn('status', [TicketStatus::InProgress])->count(),
            'overdue'        => Ticket::join('users', 'tickets.requester_id', '=', 'users.id')->where('users.department', $user->department)->overdue()->count(),
            'resolved'       => Ticket::join('users', 'tickets.requester_id', '=', 'users.id')->where('users.department', $user->department)->whereIn('status', [TicketStatus::Closed])->count(),
            'resolved_today' => Ticket::whereDate('resolved_at', today())->count(),
            'avg_resolution' => round(
                (float) Ticket::whereNotNull('resolved_at')
                    ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg')
                    ->value('avg'),
                1
            ),
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

    // ── Private helpers ───────────────────────────────────────────────────────

    private function storeAttachments(Ticket $ticket, array $files, User $uploader): void
    {
        foreach ($files as $file) {
            if (!($file instanceof UploadedFile) || !$file->isValid()) {
                continue;
            }

            try {
                $storedPath = $file->store("tickets/{$ticket->id}/attachments", 'public');

                if (!$storedPath || !Storage::disk('public')->exists($storedPath)) {
                    continue;
                }

                Attachment::create([
                    'ticket_id'     => $ticket->id,
                    'user_id'       => $uploader->id,
                    'original_name' => $file->getClientOriginalName(),
                    'stored_name'   => $storedPath,
                    'mime_type'     => $file->getMimeType() ?? $file->getClientMimeType(),
                    'size'          => $file->getSize(),
                    'disk'          => 'public',
                ]);
            } catch (\Throwable $e) {
                Log::error("Attachment storage failed for ticket #{$ticket->id}: {$e->getMessage()}");
            }
        }
    }

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
}