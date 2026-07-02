<?php

namespace App\Models;

use App\Enums\TicketCaseType;
use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\TicketStatusNew;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

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
        'case_type',
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
            'status'       => TicketStatusNew::class,
            'priority'     => TicketPriority::class,
            'case_type'    => TicketCaseType::class,
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
            $ticket->status   = $ticket->status ?? TicketStatusNew::Open;
            $ticket->sla_due_at = now()->addHours(
                $ticket->priority?->slaHours() ?? TicketPriority::Medium->slaHours()
            );
        });

        static::updating(function (Ticket $ticket) {
            if ($ticket->isDirty('sla_due_at')) {
                $ticket->sla_breached = false;
            }
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

    public function holds(): HasMany       
    { 
        return $this->hasMany(TicketHold::class)->latest('held_at'); 
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeOpen(Builder $q): Builder
    {
        return $q->where('status', TicketStatusNew::Open);
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
        return $q->whereNotIn('status', [TicketStatus::Resolved])
                 ->where('sla_due_at', '<', now());
    }

    public function scopeSearch(Builder $q, string $term): Builder
    {
        return $q->where(function ($q) use ($term) {
            $q->where('tickets.subject', 'like', "%{$term}%")
            ->orWhere('tickets.description', 'like', "%{$term}%")
            ->orWhere('tickets.id', $term);
        });
    }


    public function scopeDateRange(Builder $q, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $q->where('created_at', '>=', Carbon::parse($from)->startOfDay());
        }
    
        if ($to) {
            $q->where('created_at', '<=', Carbon::parse($to)->endOfDay());
        }
    
        return $q;
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
        $date = $this->created_at->format('ymd');
        $sequence = str_pad($this->id, 3, '0', STR_PAD_LEFT);

        return "#{$date}8{$sequence}";
    }

    public function canTransitionTo(TicketStatusNew $newStatus): bool
    {
        return in_array($newStatus, $this->status->transitions());
    }
}