<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'comment_id',   // null = ticket-level attachment, set = comment attachment
        'user_id',
        'original_name',
        'stored_name',
        'mime_type',
        'size',
        'disk',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->stored_name);
    }

    public function humanSize(): string
    {
        $b = $this->size;
        if ($b < 1024)    return $b . ' B';
        if ($b < 1048576) return round($b / 1024, 1) . ' KB';
        return round($b / 1048576, 1) . ' MB';
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function extensionLabel(): string
    {
        return strtoupper(pathinfo($this->original_name, PATHINFO_EXTENSION));
    }

    public function badgeColor(): string
    {
        return match($this->extensionLabel()) {
            'PDF'           => 'bg-red-100 text-red-700',
            'DOC', 'DOCX'   => 'bg-blue-100 text-blue-700',
            'XLS', 'XLSX'   => 'bg-green-100 text-green-700',
            'TXT'           => 'bg-gray-100 text-gray-600',
            'ZIP'           => 'bg-amber-100 text-amber-700',
            'JPG', 'JPEG',
            'PNG', 'GIF'    => 'bg-purple-100 text-purple-700',
            default         => 'bg-gray-100 text-gray-600',
        };
    }
}