<?php

namespace App\Http\Controllers;

use App\Enums\AssetCategory;
use App\Enums\AssetStatus;
use App\Http\Requests\Asset\StoreAssetRequest;
use App\Http\Requests\Asset\UpdateAssetRequest;
use App\Http\Requests\Asset\AssignAssetRequest;
use App\Http\Requests\Asset\StoreMaintenanceRequest;
use App\Models\Asset;
use App\Models\User;
use App\Services\AssetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AssetController extends Controller
{
    use AuthorizesRequests;
    
    public function __construct(
        private readonly AssetService $service
    ) {}
    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Asset::with('assignedUser')
            ->when($request->filled('search'),   fn ($q) => $q->search($request->search))
            ->when($request->filled('status'),   fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->when($request->filled('assigned'), function ($q) use ($request) {
                $request->assigned === 'yes' ? $q->assigned() : $q->unassigned();
            })
            ->when($request->boolean('warranty_expiring'), fn ($q) => $q->warrantyExpiringSoon(30))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('assets.index', [
            'assets'     => $query,
            'statuses'   => AssetStatus::cases(),
            'categories' => AssetCategory::cases(),
            'metrics'    => $this->service->metrics(),
        ]);
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function create(): View
    {
        return view('assets.create', [
            'categories' => AssetCategory::cases(),
            'statuses'   => AssetStatus::cases(),
        ]);
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function store(StoreAssetRequest $request): RedirectResponse
    {
        $asset = $this->service->create($request->validated(), $request->user());

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', "Asset {$asset->asset_tag} created successfully.");
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(Asset $asset): View
    {
        $asset->load([
            'assignedUser',
            'assignments.user',
            'assignments.assignedBy',
            'maintenances.performedBy',
            'tickets.requester',
        ]);

        return view('assets.show', [
            'asset'   => $asset,
            'users'   => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function edit(Asset $asset): View
    {
        return view('assets.edit', [
            'asset'      => $asset,
            'categories' => AssetCategory::cases(),
            'statuses'   => AssetStatus::cases(),
        ]);
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function update(UpdateAssetRequest $request, Asset $asset): RedirectResponse
    {
        $this->service->update($asset, $request->validated(), $request->user());

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', 'Asset updated successfully.');
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function destroy(Asset $asset): RedirectResponse
    {
        $this->authorize('delete', $asset);
        $tag = $asset->asset_tag;
        $asset->delete();

        return redirect()
            ->route('assets.index')
            ->with('success', "Asset {$tag} deleted.");
    }

    // ── Assign to user ────────────────────────────────────────────────────────

    public function assign(AssignAssetRequest $request, Asset $asset): RedirectResponse
    {
        $data    = $request->validated();
        $assignee = User::findOrFail($data['user_id']);

        $this->service->assignTo($asset, $assignee, $request->user(), $data['notes'] ?? null);

        return back()->with('success', "Asset assigned to {$assignee->name}.");
    }

    // ── Return asset ──────────────────────────────────────────────────────────

    public function returnAsset(Request $request, Asset $asset): RedirectResponse
    {
        $request->validate(['notes' => 'nullable|string|max:500']);

        $this->service->returnAsset($asset, $request->user(), $request->notes);

        return back()->with('success', 'Asset returned and marked as In Storage.');
    }

    // ── Log maintenance ───────────────────────────────────────────────────────

    public function storeMaintenance(StoreMaintenanceRequest $request, Asset $asset): RedirectResponse
    {
        $this->service->logMaintenance($asset, $request->validated(), $request->user());

        return back()->with('success', 'Maintenance record logged.');
    }

    // ── Retire ────────────────────────────────────────────────────────────────

    public function retire(Request $request, Asset $asset): RedirectResponse
    {
        $this->service->retire($asset, $request->user());

        return back()->with('success', "Asset {$asset->asset_tag} marked as Retired.");
    }
}