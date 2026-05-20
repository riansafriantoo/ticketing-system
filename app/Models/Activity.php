<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'action',    // e.g. 'status_changed', 'assigned', 'comment_added'
        'meta',      // JSON with before/after data
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'meta'       => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function description(): string
    {
        $actor = $this->user?->name ?? 'System';
        $meta  = $this->meta ?? [];

        return match($this->action) {
            'created'        => "{$actor} created the ticket",
            'status_changed' => "{$actor} changed status from <strong>{$meta['from']}</strong> to <strong>{$meta['to']}</strong>",
            'assigned'       => "{$actor} assigned ticket to <strong>{$meta['to']}</strong>",
            'priority_changed' => "{$actor} changed priority to <strong>{$meta['to']}</strong>",
            'comment_added'  => "{$actor} added a comment",
            'sla_breached'   => "⚠️ SLA breached",
            default          => "{$actor} performed action: {$this->action}",
        };
    }
}