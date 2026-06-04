<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetAssignment extends Model
{
    protected $fillable = [
        'asset_id',
        'user_id',         // who was assigned
        'assigned_by',     // admin/agent who made the assignment
        'assigned_at',
        'returned_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at'  => 'datetime',
            'returned_at'  => 'datetime',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function isActive(): bool
    {
        return is_null($this->returned_at);
    }

    public function durationDays(): ?int
    {
        $end = $this->returned_at ?? now();
        return (int) $this->assigned_at->diffInDays($end);
    }
}