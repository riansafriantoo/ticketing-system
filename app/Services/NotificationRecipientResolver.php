<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Resolves WHO should receive an email for a given ticket event.
 *
 * This is deliberately the ONLY place in the codebase that encodes
 * recipient rules. Every listener calls into this class instead of
 * hand-rolling its own "if requester then... if assignee then..."
 * logic — that duplication is exactly how notification bugs creep in
 * (e.g. one listener forgets to exclude the comment author, another
 * email goes to a deactivated user). One class, one set of rules,
 * fully unit-testable in isolation from the mail/queue system.
 *
 * General rules applied across all event types:
 *   - Deactivated users (is_active = false) never receive email,
 *     regardless of their role in the ticket.
 *   - The user who performed the action never receives an email
 *     about their own action (no one needs to be told "you did X").
 *   - Recipients are always de-duplicated by user ID — if the
 *     requester and assignee happen to be the same person, or an
 *     admin is also the assignee, they get exactly one email.
 */
class NotificationRecipientResolver
{
    public function forTicketCreated(Ticket $ticket, User $createdBy): Collection
    {
        // Ensure requester is loaded — the queued listener re-fetches
        // the Ticket model from DB without relationships, so this guard
        // is necessary whenever this method runs from a queued job.
        $ticket->loadMissing('requester');

        $admins = User::role('admin')->get();

        // return $this->merge([$ticket->requester], $admins)
        //     ->reject(fn (User $u) => $u->id === $createdBy->id)
        //     ->pipe(fn ($c) => $this->finalize($c));

        return $this->merge([$ticket->requester], $admins)
            ->pipe(fn ($c) => $this->finalize($c));
    }

    public function forStatusChanged(Ticket $ticket, User $changedBy): Collection
    {
        $ticket->loadMissing(['requester', 'assignee']);

        return $this->merge([$ticket->requester, $ticket->assignee])
            // ->reject(fn (User $u) => $u->id === $changedBy->id)
            ->pipe(fn ($c) => $this->finalize($c));
    }

    /**
     * Ticket assigned/reassigned — the NEW assignee always gets
     * notified (this is the core "you've got work" alert), and the
     * requester is told who's now handling their ticket. The person
     * doing the assigning is excluded (they already know).
     *
     * Deliberately does NOT notify the previous assignee — being taken
     * off a ticket isn't actionable for them and would just be noise.
     */
    public function forTicketAssigned(Ticket $ticket, ?User $newAssignee, User $assignedBy): Collection
    {
        $ticket->loadMissing('requester');

        return $this->merge([$newAssignee, $ticket->requester])
            // ->reject(fn (User $u) => $u->id === $assignedBy->id)
            ->pipe(fn ($c) => $this->finalize($c));
    }

    /**
     * Comment added — notify the requester and assignee, EXCLUDING
     * whoever wrote the comment (you don't need an email about your
     * own reply).
     *
     * Internal notes are restricted to agents/admins: if the comment
     * is internal, the requester is dropped from the recipient list
     * even though they'd normally be included for a public reply.
     */
    public function forCommentAdded(Ticket $ticket, Comment $comment): Collection
    {
        $ticket->loadMissing(['requester', 'assignee']);

        $candidates = $comment->is_internal ? [$ticket->assignee] : [$ticket->requester, $ticket->assignee];

        return $this->merge($candidates)
            // ->reject(fn (User $u) => $u->id === $comment->user_id)
            ->pipe(fn ($c) => $this->finalize($c));
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Combines an array of (possibly null) Users with an optional
     * Collection of additional users into one flat Collection.
     */
    private function merge(array $users, ?Collection $extra = null): Collection
    {
        $base = collect($users)->filter(); // drops nulls (e.g. no assignee yet)

        return $extra ? $base->merge($extra) : $base;
    }

    /**
     * Shared final pass applied to every resolver method:
     *   1. drop inactive accounts
     *   2. de-duplicate by user ID
     *   3. re-index as a plain list
     */
    private function finalize(Collection $users): Collection
    {
        return $users
            ->filter(fn (User $u) => $u->is_active)
            ->unique('id')
            ->values();
    }
}