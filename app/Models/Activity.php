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
            'reassigned_due_to_deactivation' =>
                "<strong>{$actor}</strong> reassigned this ticket from ".
                "<strong>{$meta['from']}</strong> to <strong>{$meta['to']}</strong> ". 
                '<span class="px-1.5 py-0.5 text-[10px] font-semibold rounded bg-amber-100 text-amber-700">account deactivated</span>',
            'user_deactivated' =>
                "<strong>{$actor}</strong> deactivated user <strong>{$meta['deactivated_user']}</strong>"
                . (($meta['tickets_reassigned'] ?? 0) > 0
                    ? " and reassigned {$meta['tickets_reassigned']} open ticket(s)"
                    : ''),
            'user_reactivated' =>
                "<strong>{$actor}</strong> reactivated user <strong>{$meta['reactivated_user']}</strong>",
            default          => "{$actor} performed action: {$this->action}",
        };
    }
}