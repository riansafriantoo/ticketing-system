<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketHold extends Model
{
    protected $fillable = [
        'ticket_id',
        'held_by',
        'held_at',
        'resumed_at',
        'duration_minutes',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'held_at'    => 'datetime',
            'resumed_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function heldBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'held_by');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->whereNull('resumed_at');
    }

    public function isActive(): bool
    {
        return is_null($this->resumed_at);
    }

    public function durationLabel(): string
    {
        $minutes = $this->duration_minutes
            ?? (int) $this->held_at->diffInMinutes(now());

        return Ticket::formatMinutes($minutes);
    }
}
