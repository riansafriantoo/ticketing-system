<?php

namespace App\Models;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'subject',
        'description',
        'status',
        'priority',
        'category',
        'requester_id',
        'assignee_id',
        'sla_due_at',
        'resolved_at',
        'closed_at',
        'sla_breached',
    ];

    protected function casts(): array
    {
        return [
            'status'       => TicketStatus::class,
            'priority'     => TicketPriority::class,
            'category'     => TicketCategory::class,
            'sla_due_at'   => 'datetime',
            'resolved_at'  => 'datetime',
            'closed_at'    => 'datetime',
            'sla_breached' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            $ticket->uuid     = $ticket->uuid ?? (string) Str::uuid();
            $ticket->status   = $ticket->status ?? TicketStatus::Open;
            $ticket->sla_due_at = now()->addHours(
                $ticket->priority?->slaHours() ?? TicketPriority::Medium->slaHours()
            );
        });
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->latest();
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeOpen(Builder $q): Builder
    {
        return $q->where('status', TicketStatus::Open);
    }

    public function scopeAssignedTo(Builder $q, int $userId): Builder
    {
        return $q->where('assignee_id', $userId);
    }

    public function scopeForRequester(Builder $q, int $userId): Builder
    {
        return $q->where('requester_id', $userId);
    }

    public function scopeOverdue(Builder $q): Builder
    {
        return $q->whereNotIn('status', [TicketStatus::Resolved, TicketStatus::Closed])
                 ->where('sla_due_at', '<', now());
    }

    public function scopeSearch(Builder $q, string $term): Builder
    {
        return $q->where(function ($q) use ($term) {
            $q->where('subject', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhere('id', $term);
        });
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isOverdue(): bool
    {
        return !$this->status->isTerminal() && $this->sla_due_at?->isPast();
    }

    public function slaRemainingHours(): ?float
    {
        if (!$this->sla_due_at) {
            return null;
        }

        return round($this->sla_due_at->diffInHours(now(), false), 1);
    }

    public function ticketNumber(): string
    {
        return 'TKT-' . str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }

    public function canTransitionTo(TicketStatus $newStatus): bool
    {
        return in_array($newStatus, $this->status->transitions());
    }
}