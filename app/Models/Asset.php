<?php

namespace App\Models;

use App\Enums\AssetCategory;
use App\Enums\AssetStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use app\Models\AssetAssignment;
use app\Models\AssetMaintenance;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_tag',
        'name',
        'description',
        'category',
        'status',
        'brand',
        'model',
        'serial_number',
        'purchase_date',
        'purchase_cost',
        'warranty_expiry',
        'location',
        'assigned_to',
        'assigned_at',
        'notes',
        'image',
    ];

    protected function casts(): array
    {
        return [
            'category'       => AssetCategory::class,
            'status'         => AssetStatus::class,
            'purchase_date'  => 'date',
            'purchase_cost'  => 'decimal:2',
            'warranty_expiry'=> 'date',
            'assigned_at'    => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Asset $asset) {
            if (empty($asset->asset_tag)) {
                $asset->asset_tag = self::generateAssetTag();
            }
            if (empty($asset->status)) {
                $asset->status = AssetStatus::Active;
            }
        });
    }

    public static function generateAssetTag(): string
    {
        do {
            $tag = 'AST-' . strtoupper(Str::random(6));
        } while (self::where('asset_tag', $tag)->exists());

        return $tag;
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class)->latest();
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class)->latest();
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class)->latest();
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeSearch(Builder $q, string $term): Builder
    {
        return $q->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('asset_tag', 'like', "%{$term}%")
              ->orWhere('serial_number', 'like', "%{$term}%")
              ->orWhere('brand', 'like', "%{$term}%")
              ->orWhere('model', 'like', "%{$term}%");
        });
    }

    public function scopeAssigned(Builder $q): Builder
    {
        return $q->whereNotNull('assigned_to');
    }

    public function scopeUnassigned(Builder $q): Builder
    {
        return $q->whereNull('assigned_to');
    }

    public function scopeWarrantyExpiringSoon(Builder $q, int $days = 30): Builder
    {
        return $q->whereNotNull('warranty_expiry')
                 ->whereBetween('warranty_expiry', [now(), now()->addDays($days)]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isAssigned(): bool
    {
        return !is_null($this->assigned_to);
    }

    public function isWarrantyExpired(): bool
    {
        return $this->warranty_expiry && $this->warranty_expiry->isPast();
    }

    public function isWarrantyExpiringSoon(int $days = 30): bool
    {
        return $this->warranty_expiry
            && !$this->isWarrantyExpired()
            && $this->warranty_expiry->diffInDays(now()) <= $days;
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}