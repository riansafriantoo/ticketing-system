<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Services\TicketService;
use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CommentController extends Controller
{
    /**
     * Store a new comment, optionally with file attachments.
     */
    public function store(Request $request, Ticket $ticket): RedirectResponse
    {
        // $this->authorize('view', $ticket);

        // ── Validate text fields ──────────────────────────────────────────────
        $data = $request->validate([
            'body'        => 'required|string|max:10000',
            'is_internal' => 'boolean',
        ]);

        // ── Validate file attachments separately ──────────────────────────────
        $request->validate([
            'attachments'   => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt,zip',
        ]);

        $isInternal = $request->user()->isAgent() && ($data['is_internal'] ?? false);

        // ── Create comment ────────────────────────────────────────────────────
        $comment =  app(TicketService::class)->addComment(
                        $ticket,            // Ticket $ticket
                        $request->user(),   // User $author
                        $data['body'],       // string $body
                        $isInternal,         // bool $isInternal
                    );

        // ── Store attachments ─────────────────────────────────────────────────
        $files = $request->file('attachments') ?? [];
        $this->storeCommentAttachments($comment, $ticket, $files, $request->user()->id);

        // ── Log activity ──────────────────────────────────────────────────────
        Activity::create([
            'ticket_id'  => $ticket->id,
            'user_id'    => $request->user()->id,
            'action'     => 'comment_added',
            'meta'       => [
                'is_internal' => $isInternal,
                'has_files'   => count($files) > 0,
            ],
            'created_at' => now(),
        ]);

        return back()->with('success', 'Reply added.');
    }

    /**
     * Delete a comment (and cascade-delete its attachments from disk).
     */
    public function destroy(Ticket $ticket, Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        foreach ($comment->attachments as $attachment) {
            $this->deleteFromDisk($attachment);
        }

        $comment->delete();

        return back()->with('success', 'Comment deleted.');
    }

    /**
     * Delete a single attachment that belongs to a comment.
     * Only the comment author, agents, or admins may do this.
     */
    public function destroyAttachment(Ticket $ticket, Comment $comment, Attachment $attachment): RedirectResponse
    {
        // Guard: attachment must belong to this comment
        if ($attachment->comment_id !== $comment->id) {
            abort(404);
        }

        // Guard: only the uploader, agents, or admins may delete
        $user = request()->user();
        if ($attachment->user_id !== $user->id && !$user->isAgent()) {
            abort(403);
        }

        $this->deleteFromDisk($attachment);
        $attachment->delete();

        return back()->with('success', 'Attachment removed.');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * @param  UploadedFile[] $files
     */
    private function storeCommentAttachments(
        Comment $comment,
        Ticket  $ticket,
        array   $files,
        int     $userId
    ): void {
        foreach ($files as $file) {

            if (!($file instanceof UploadedFile) || !$file->isValid()) {
                Log::warning("Skipped invalid comment attachment", [
                    'ticket_id'  => $ticket->id,
                    'comment_id' => $comment->id,
                ]);
                continue;
            }

            try {
                $path = $file->store(
                    "tickets/{$ticket->id}/comments/{$comment->id}",
                    'public'
                );

                if (!$path || !Storage::disk('public')->exists($path)) {
                    Log::error("Comment attachment store() failed", [
                        'ticket_id'  => $ticket->id,
                        'comment_id' => $comment->id,
                        'file'       => $file->getClientOriginalName(),
                    ]);
                    continue;
                }

                Attachment::create([
                    'ticket_id'     => $ticket->id,
                    'comment_id'    => $comment->id,
                    'user_id'       => $userId,
                    'original_name' => $file->getClientOriginalName(),
                    'stored_name'   => $path,
                    'mime_type'     => $file->getMimeType() ?? $file->getClientMimeType(),
                    'size'          => $file->getSize(),
                    'disk'          => 'public',
                ]);

            } catch (\Throwable $e) {
                Log::error("Comment attachment failed: {$e->getMessage()}", [
                    'ticket_id'  => $ticket->id,
                    'comment_id' => $comment->id,
                    'file'       => $file->getClientOriginalName(),
                ]);
            }
        }
    }

    private function deleteFromDisk(Attachment $attachment): void
    {
        try {
            if (Storage::disk($attachment->disk)->exists($attachment->stored_name)) {
                Storage::disk($attachment->disk)->delete($attachment->stored_name);
            }
        } catch (\Throwable $e) {
            Log::warning("Could not delete attachment from disk: {$e->getMessage()}");
        }
    }
}