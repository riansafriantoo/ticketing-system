@extends('layouts.app')
@section('title', $asset->asset_tag)
@section('heading', $asset->name)
@section('subheading', $asset->asset_tag . ' · ' . $asset->category->label())

@section('header-actions')
<a href="{{ route('assets.edit', $asset) }}"
   class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
    </svg>
    Edit
</a>
@endsection

@section('content')
<div class="flex gap-6 items-start">

    {{-- ── Left — details + tabs ────────────────────────────────────────────── --}}
    <div class="flex-1 min-w-0 space-y-4">

        {{-- Status bar --}}
        <div class="bg-white rounded-xl border border-gray-100 p-4 flex items-center gap-4 flex-wrap">
            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold {{ $asset->status->badgeClass() }}">
                {{ $asset->status->label() }}
            </span>
            <span class="text-xs text-gray-400">{{ $asset->category->icon() }} {{ $asset->category->label() }}</span>
            @if($asset->brand)
            <span class="text-xs text-gray-400">{{ $asset->brand }} {{ $asset->model }}</span>
            @endif
            @if($asset->serial_number)
            <span class="font-mono text-xs text-gray-400">S/N: {{ $asset->serial_number }}</span>
            @endif
            @if($asset->isWarrantyExpired())
            <span class="text-xs font-medium text-red-500">⚠️ Warranty Expired</span>
            @elseif($asset->isWarrantyExpiringSoon())
            <span class="text-xs font-medium text-amber-600">⚠️ Warranty Expiring Soon</span>
            @endif
        </div>

        {{-- Tab navigation --}}
        <div x-data="{ tab: 'overview' }" class="space-y-4">
            <div class="flex gap-1 bg-gray-100 p-1 rounded-xl w-fit">
                @foreach([['overview','Overview'],['history','Assignment History'],['maintenance','Maintenance'],['tickets','Linked Tickets']] as [$t,$l])
                <button @click="tab = '{{ $t }}'"
                        :class="tab === '{{ $t }}' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all">
                    {{ $l }}
                </button>
                @endforeach
            </div>

            {{-- ── Overview tab ─────────────────────────────────────────────── --}}
            <div x-show="tab === 'overview'" class="space-y-4">
                <div class="bg-white rounded-xl border border-gray-100 p-5">
                    <div class="flex items-start gap-5">
                        @if($asset->imageUrl())
                        <img src="{{ $asset->imageUrl() }}" class="w-28 h-28 rounded-xl object-cover border border-gray-100 flex-shrink-0" alt="">
                        @else
                        <div class="w-28 h-28 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-center text-5xl flex-shrink-0">
                            {{ $asset->category->icon() }}
                        </div>
                        @endif

                        <div class="flex-1 grid grid-cols-2 gap-x-8 gap-y-3 text-sm">
                            @php
                            $details = [
                                'Asset Tag'      => $asset->asset_tag,
                                'Location'       => $asset->location ?? '—',
                                'Purchase Date'  => $asset->purchase_date?->format('M d, Y') ?? '—',
                                'Purchase Cost'  => $asset->purchase_cost ? '$'.number_format($asset->purchase_cost, 2) : '—',
                                'Warranty'       => $asset->warranty_expiry?->format('M d, Y') ?? '—',
                                'Registered'     => $asset->created_at->format('M d, Y'),
                            ];
                            @endphp
                            @foreach($details as $label => $value)
                            <div>
                                <p class="text-[11px] text-gray-400 font-medium uppercase tracking-wide">{{ $label }}</p>
                                <p class="text-gray-800 mt-0.5">{{ $value }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    @if($asset->description)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Description</p>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ $asset->description }}</p>
                    </div>
                    @endif

                    @if($asset->notes)
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Notes</p>
                        <p class="text-sm text-gray-600">{{ $asset->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ── Assignment history tab ────────────────────────────────────── --}}
            <div x-show="tab === 'history'" class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Assignment History</h3>
                    <span class="text-xs text-gray-400">{{ $asset->assignments->count() }} record(s)</span>
                </div>
                @if($asset->assignments->isEmpty())
                <div class="py-10 text-center"><p class="text-sm text-gray-400">No assignment history yet.</p></div>
                @else
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-5 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">User</th>
                            <th class="text-left px-4 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Assigned</th>
                            <th class="text-left px-4 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Returned</th>
                            <th class="text-left px-4 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Duration</th>
                            <th class="text-left px-4 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($asset->assignments as $asgn)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $asgn->user->avatarUrl() }}" class="w-6 h-6 rounded-full" alt="">
                                    <span class="text-sm text-gray-800">{{ $asgn->user->name }}</span>
                                    @if($asgn->isActive())
                                    <span class="px-1.5 py-0.5 bg-green-100 text-green-700 text-[10px] font-medium rounded">Current</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">{{ $asgn->assigned_at->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600">{{ $asgn->returned_at?->format('M d, Y') ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600">{{ $asgn->durationDays() }} days</td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $asgn->assignedBy?->name ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

            {{-- ── Maintenance tab ──────────────────────────────────────────── --}}
            <div x-show="tab === 'maintenance'" class="space-y-4">
                {{-- Log maintenance form --}}
                <div class="bg-white rounded-xl border border-gray-100 p-5">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Log Maintenance</h3>
                    <form method="POST" action="{{ route('assets.maintenance.store', $asset) }}" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Type <span class="text-red-500">*</span></label>
                                <select name="type" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:border-brand-400">
                                    <option value="service">Service</option>
                                    <option value="repair">Repair</option>
                                    <option value="upgrade">Upgrade</option>
                                    <option value="inspection">Inspection</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Date <span class="text-red-500">*</span></label>
                                <input type="date" name="performed_at" value="{{ date('Y-m-d') }}"
                                       class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Cost ($)</label>
                                <input type="number" name="cost" step="0.01" min="0" placeholder="0.00"
                                       class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Vendor</label>
                                <input type="text" name="vendor" placeholder="Service provider"
                                       class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Next Maintenance</label>
                                <input type="date" name="next_maintenance_at"
                                       class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Description <span class="text-red-500">*</span></label>
                            <textarea name="description" rows="2" required
                                      class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 resize-none focus:outline-none focus:border-brand-400"></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                                Log Record
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Maintenance history --}}
                @if($asset->maintenances->isEmpty())
                <div class="bg-white rounded-xl border border-gray-100 py-10 text-center">
                    <p class="text-sm text-gray-400">No maintenance records yet.</p>
                </div>
                @else
                <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Maintenance History</h3>
                    </div>
                    <div class="divide-y divide-gray-50">
                        @foreach($asset->maintenances as $maint)
                        <div class="px-5 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="inline-flex px-2 py-0.5 rounded-md text-[11px] font-medium {{ $maint->typeBadgeClass() }}">
                                            {{ $maint->typeLabel() }}
                                        </span>
                                        <span class="text-xs text-gray-500">{{ $maint->performed_at->format('M d, Y') }}</span>
                                        @if($maint->vendor)
                                        <span class="text-xs text-gray-400">· {{ $maint->vendor }}</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-700">{{ $maint->description }}</p>
                                    @if($maint->next_maintenance_at)
                                    <p class="text-xs text-gray-400 mt-1">Next: {{ $maint->next_maintenance_at->format('M d, Y') }}</p>
                                    @endif
                                </div>
                                <div class="text-right flex-shrink-0">
                                    @if($maint->cost)
                                    <p class="text-sm font-semibold text-gray-900">${{ number_format($maint->cost, 2) }}</p>
                                    @endif
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $maint->performedBy?->name ?? '—' }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- ── Linked Tickets tab ───────────────────────────────────────── --}}
            <div x-show="tab === 'tickets'" class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Linked Support Tickets</h3>
                </div>
                @if($asset->tickets->isEmpty())
                <div class="py-10 text-center"><p class="text-sm text-gray-400">No tickets linked to this asset.</p></div>
                @else
                <table class="min-w-full">
                    <tbody class="divide-y divide-gray-50">
                        @foreach($asset->tickets as $ticket)
                        <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('tickets.show', $ticket) }}'">
                            <td class="px-5 py-3 w-28"><span class="font-mono text-xs text-gray-500">{{ $ticket->ticketNumber() }}</span></td>
                            <td class="px-4 py-3"><p class="text-sm text-gray-800">{{ $ticket->subject }}</p></td>
                            <td class="px-4 py-3 w-28">
                                <span class="inline-flex px-2 py-0.5 rounded-md text-[11px] font-medium {{ $ticket->status->badgeClass() }}">
                                    {{ $ticket->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 w-28"><span class="text-xs text-gray-400">{{ $ticket->created_at->format('M d, Y') }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Right sidebar ─────────────────────────────────────────────────────── --}}
    <aside class="w-72 flex-shrink-0 space-y-4">

        {{-- Current assignment --}}
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-3">
                {{ $asset->isAssigned() ? 'Currently Assigned To' : 'Assignment' }}
            </p>

            @if($asset->isAssigned())
            <div class="flex items-center gap-3 mb-3">
                <img src="{{ $asset->assignedUser->avatarUrl() }}" class="w-9 h-9 rounded-full" alt="">
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $asset->assignedUser->name }}</p>
                    <p class="text-xs text-gray-400">{{ $asset->assignedUser->department ?? $asset->assignedUser->email }}</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">Since {{ $asset->assigned_at?->format('M d, Y') }}</p>
                </div>
            </div>
            {{-- Return form --}}
            <form method="POST" action="{{ route('assets.return', $asset) }}">
                @csrf
                <input type="hidden" name="notes" value="Asset returned via admin panel">
                <button type="submit"
                        onclick="return confirm('Mark this asset as returned?')"
                        class="w-full flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-medium text-amber-700 border border-amber-200 rounded-lg hover:bg-amber-50 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
                    Return Asset
                </button>
            </form>
            @else
            {{-- Assign form --}}
            <form method="POST" action="{{ route('assets.assign', $asset) }}" class="space-y-2">
                @csrf
                <select name="user_id" required
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:border-brand-400">
                    <option value="">Select user…</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
                <input type="text" name="notes" placeholder="Notes (optional)"
                       class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400">
                <button type="submit"
                        class="w-full flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-medium text-white bg-brand-600 rounded-lg hover:bg-brand-700">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Assign Asset
                </button>
            </form>
            @endif
        </div>

        {{-- Quick stats --}}
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-3">Quick Stats</p>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-gray-500">Times assigned</span>
                    <span class="font-semibold text-gray-900">{{ $asset->assignments->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Maintenance records</span>
                    <span class="font-semibold text-gray-900">{{ $asset->maintenances->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Total maintenance cost</span>
                    <span class="font-semibold text-gray-900">${{ number_format($asset->maintenances->sum('cost'), 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Linked tickets</span>
                    <span class="font-semibold text-gray-900">{{ $asset->tickets->count() }}</span>
                </div>
            </div>
        </div>

        {{-- Admin actions --}}
        @if(auth()->user()->isAdmin())
        <div class="bg-white rounded-xl border border-gray-100 p-4 space-y-2">
            <a href="{{ route('assets.edit', $asset) }}"
               class="flex items-center justify-center gap-1.5 w-full px-3 py-2 text-xs font-medium text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Asset
            </a>

            @if($asset->status->value !== 'retired')
            <form method="POST" action="{{ route('assets.retire', $asset) }}"
                  onsubmit="return confirm('Retire this asset? This will unassign it from any current user.')">
                @csrf
                <button type="submit"
                        class="flex items-center justify-center gap-1.5 w-full px-3 py-2 text-xs font-medium text-amber-700 border border-amber-200 rounded-lg hover:bg-amber-50">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                    Retire Asset
                </button>
            </form>
            @endif

            <form method="POST" action="{{ route('assets.destroy', $asset) }}"
                  onsubmit="return confirm('Permanently delete {{ addslashes($asset->asset_tag) }}? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="flex items-center justify-center gap-1.5 w-full px-3 py-2 text-xs font-medium text-red-600 border border-red-100 rounded-lg hover:bg-red-50">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete Asset
                </button>
            </form>
        </div>
        @endif
    </aside>
</div>
@endsection

@push('head')
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush