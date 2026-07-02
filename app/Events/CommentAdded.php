<?php

namespace App\Events;

use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a new reply or internal note is added to a ticket.
 *
 * The comment's own `is_internal` flag and `user_id` (already on the
 * model) are enough for the listener/resolver to decide who should
 * see it — internal notes only reach agents/admins, and the comment's
 * author never receives a notification about their own comment.
 */
class CommentAdded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Ticket $ticket,
        public readonly Comment $comment,
    ) {}
}