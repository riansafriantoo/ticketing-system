<?php

namespace App\Services;

use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssetService
{
    // ── Create ────────────────────────────────────────────────────────────────

    public function create(array $data, User $actor): Asset
    {
        return DB::transaction(function () use ($data, $actor) {
            $image = null;
            if (!empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $image = $data['image']->store('assets/images', 'public');
            }

            return Asset::create([
                ...$data,
                'image'  => $image,
                'status' => $data['status'] ?? AssetStatus::Active->value,
            ]);
        });
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function update(Asset $asset, array $data, User $actor): Asset
    {
        return DB::transaction(function () use ($asset, $data, $actor) {
            if (!empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $data['image'] = $data['image']->store('assets/images', 'public');
            } else {
                unset($data['image']);
            }

            $asset->update($data);
            return $asset->fresh();
        });
    }

    // ── Assign to user ────────────────────────────────────────────────────────

    public function assignTo(Asset $asset, User $assignee, User $actor, ?string $notes = null): Asset
    {
        return DB::transaction(function () use ($asset, $assignee, $actor, $notes) {
            // Close any existing active assignment
            $this->returnActiveAssignment($asset, $actor);

            // Create new assignment history record
            AssetAssignment::create([
                'asset_id'    => $asset->id,
                'user_id'     => $assignee->id,
                'assigned_by' => $actor->id,
                'assigned_at' => now(),
                'notes'       => $notes,
            ]);

            // Update asset's current state
            $asset->update([
                'assigned_to' => $assignee->id,
                'assigned_at' => now(),
                'status'      => AssetStatus::Active->value,
            ]);

            return $asset->fresh(['assignedUser']);
        });
    }

    // ── Return / unassign ─────────────────────────────────────────────────────

    public function returnAsset(Asset $asset, User $actor, ?string $notes = null): Asset
    {
        return DB::transaction(function () use ($asset, $actor, $notes) {
            $this->returnActiveAssignment($asset, $actor, $notes);

            $asset->update([
                'assigned_to' => null,
                'assigned_at' => null,
                'status'      => AssetStatus::InStorage->value,
            ]);

            return $asset->fresh();
        });
    }

    // ── Log maintenance ───────────────────────────────────────────────────────

    public function logMaintenance(Asset $asset, array $data, User $actor): \App\Models\AssetMaintenance
    {
        return DB::transaction(function () use ($asset, $data, $actor) {
            $record = $asset->maintenances()->create([
                ...$data,
                'performed_by' => $actor->id,
            ]);

            // If it's a repair, update status
            if ($data['type'] === 'repair') {
                $asset->update(['status' => AssetStatus::UnderRepair->value]);
            }

            return $record;
        });
    }

    // ── Retire / dispose ──────────────────────────────────────────────────────

    public function retire(Asset $asset, User $actor): Asset
    {
        return DB::transaction(function () use ($asset, $actor) {
            // Return if currently assigned
            if ($asset->isAssigned()) {
                $this->returnActiveAssignment($asset, $actor, 'Asset retired');
            }

            $asset->update([
                'assigned_to' => null,
                'assigned_at' => null,
                'status'      => AssetStatus::Retired->value,
            ]);

            return $asset->fresh();
        });
    }

    // ── Metrics for dashboard ─────────────────────────────────────────────────

    public function metrics(): array
    {
        return [
            'total'             => Asset::count(),
            'assigned'          => Asset::assigned()->count(),
            'unassigned'        => Asset::unassigned()->where('status', AssetStatus::Active->value)->count(),
            'under_repair'      => Asset::where('status', AssetStatus::UnderRepair->value)->count(),
            'warranty_expiring' => Asset::warrantyExpiringSoon(30)->count(),
            'total_value'       => Asset::sum('purchase_cost'),
        ];
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function returnActiveAssignment(Asset $asset, User $actor, ?string $notes = null): void
    {
        AssetAssignment::where('asset_id', $asset->id)
            ->whereNull('returned_at')
            ->update([
                'returned_at' => now(),
                'notes'       => $notes,
            ]);
    }
}