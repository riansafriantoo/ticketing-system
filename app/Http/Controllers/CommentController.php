<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\Activity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CommentController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Ticket $ticket): RedirectResponse
    {
        // $this->authorize('view', $ticket);

        $data = $request->validate([
            'body'        => 'required|string|max:10000',
            'is_internal' => 'boolean',
        ]);

        $isInternal = $request->user()->isAgent() && ($data['is_internal'] ?? false);

        $ticket->comments()->create([
            'user_id'     => $request->user()->id,
            'body'        => $data['body'],
            'is_internal' => $isInternal,
        ]);

        Activity::create([
            'ticket_id'  => $ticket->id,
            'user_id'    => $request->user()->id,
            'action'     => 'comment_added',
            'meta'       => ['is_internal' => $isInternal],
            'created_at' => now(),
        ]);

        return back()->with('success', 'Comment added.');
    }

    public function destroy(Ticket $ticket, Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);
        $comment->delete();

        return back()->with('success', 'Comment deleted.');
    }
}