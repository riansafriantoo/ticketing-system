<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMaintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'performed_by',       // user_id of agent who logged it
        'type',               // repair | service | upgrade | inspection
        'description',
        'cost',
        'vendor',
        'performed_at',
        'next_maintenance_at',
    ];

    protected function casts(): array
    {
        return [
            'cost'               => 'decimal:2',
            'performed_at'       => 'date',
            'next_maintenance_at'=> 'date',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function typeLabel(): string
    {
        return match($this->type) {
            'repair'     => 'Repair',
            'service'    => 'Service',
            'upgrade'    => 'Upgrade',
            'inspection' => 'Inspection',
            default      => ucfirst($this->type),
        };
    }

    public function typeBadgeClass(): string
    {
        return match($this->type) {
            'repair'     => 'bg-red-100 text-red-700',
            'service'    => 'bg-blue-100 text-blue-700',
            'upgrade'    => 'bg-purple-100 text-purple-700',
            'inspection' => 'bg-gray-100 text-gray-600',
            default      => 'bg-gray-100 text-gray-600',
        };
    }
}