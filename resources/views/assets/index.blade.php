@extends('layouts.app')

@section('title', 'Assets')
@section('heading', 'Asset Management')
@section('subheading', $assets->total() . ' assets registered')

@section('header-actions')
<a href="{{ route('assets.create') }}"
   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
    </svg>
    Add Asset
</a>
@endsection

@section('content')
<div class="space-y-5">

    {{-- ── Metric cards ─────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
        @php
        $cards = [
            ['label'=>'Total Assets',      'value'=>number_format($metrics['total']),          'color'=>'text-gray-900',  'bg'=>'bg-white'],
            ['label'=>'Assigned',          'value'=>number_format($metrics['assigned']),        'color'=>'text-green-600', 'bg'=>'bg-white'],
            ['label'=>'Available',         'value'=>number_format($metrics['unassigned']),      'color'=>'text-blue-600',  'bg'=>'bg-white'],
            ['label'=>'Under Repair',      'value'=>number_format($metrics['under_repair']),    'color'=>'text-amber-600', 'bg'=>'bg-white'],
            ['label'=>'Warranty Expiring', 'value'=>number_format($metrics['warranty_expiring']),'color'=>'text-red-600',  'bg'=>'bg-white'],
        ];
        @endphp
        @foreach($cards as $card)
        <div class="{{ $card['bg'] }} rounded-xl border border-gray-100 px-5 py-3">
            <p class="text-[11px] text-gray-400 font-medium">{{ $card['label'] }}</p>
            <p class="text-xl font-semibold {{ $card['color'] }} mt-0.5">{{ $card['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- ── Filters ─────────────────────────────────────────────────────────── --}}
    <form method="GET" class="flex flex-wrap items-center gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search tag, name, serial, brand…"
               class="w-60 text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200">

        <select name="category" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus:outline-none focus:border-brand-400">
            <option value="">All categories</option>
            @foreach($categories as $c)
            <option value="{{ $c->value }}" {{ request('category') === $c->value ? 'selected' : '' }}>
                {{ $c->icon() }} {{ $c->label() }}
            </option>
            @endforeach
        </select>

        <select name="status" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus:outline-none focus:border-brand-400">
            <option value="">All statuses</option>
            @foreach($statuses as $s)
            <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>
                {{ $s->label() }}
            </option>
            @endforeach
        </select>

        <select name="assigned" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus:outline-none focus:border-brand-400">
            <option value="">All</option>
            <option value="yes" {{ request('assigned') === 'yes' ? 'selected' : '' }}>Assigned</option>
            <option value="no"  {{ request('assigned') === 'no'  ? 'selected' : '' }}>Unassigned</option>
        </select>

        <label class="flex items-center gap-1.5 text-sm text-gray-600 cursor-pointer select-none">
            <input type="checkbox" name="warranty_expiring" value="1"
                   {{ request('warranty_expiring') ? 'checked' : '' }}
                   class="rounded border-gray-300 text-brand-600 focus:ring-brand-300">
            Warranty expiring
        </label>

        <button type="submit" class="px-3 py-1.5 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
            Filter
        </button>

        @if(request()->hasAny(['search','category','status','assigned','warranty_expiring']))
        <a href="{{ route('assets.index') }}" class="px-3 py-1.5 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50">
            Clear
        </a>
        @endif
    </form>

    {{-- ── Asset table ─────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        @if($assets->isEmpty())
        <div class="flex flex-col items-center justify-center py-20">
            <div class="w-14 h-14 rounded-full bg-gray-50 flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500">No assets found</p>
            <a href="{{ route('assets.create') }}" class="mt-2 text-xs text-brand-600 hover:underline">Add your first asset →</a>
        </div>
        @else
        <table class="min-w-full">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-5 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-28">Tag</th>
                    <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Asset</th>
                    <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-28">Status</th>
                    <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-32">Category</th>
                    <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-36">Assigned To</th>
                    <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-28">Warranty</th>
                    <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-28">Location</th>
                    <th class="px-4 py-3 w-10"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($assets as $asset)
                <tr class="hover:bg-gray-50 cursor-pointer group"
                    onclick="window.location='{{ route('assets.show', $asset) }}'">

                    {{-- Tag --}}
                    <td class="px-5 py-3">
                        <span class="font-mono text-xs text-gray-500 font-medium">{{ $asset->asset_tag }}</span>
                    </td>

                    {{-- Name + brand/model --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if($asset->imageUrl())
                            <img src="{{ $asset->imageUrl() }}" class="w-8 h-8 rounded-lg object-cover border border-gray-100 flex-shrink-0" alt="">
                            @else
                            <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0 text-sm">
                                {{ $asset->category->icon() }}
                            </div>
                            @endif
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $asset->name }}</p>
                                @if($asset->brand || $asset->model)
                                <p class="text-xs text-gray-400">{{ implode(' ', array_filter([$asset->brand, $asset->model])) }}</p>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- Status --}}
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium {{ $asset->status->badgeClass() }}">
                            {{ $asset->status->label() }}
                        </span>
                    </td>

                    {{-- Category --}}
                    <td class="px-4 py-3">
                        <span class="text-xs text-gray-600">{{ $asset->category->label() }}</span>
                    </td>

                    {{-- Assigned to --}}
                    <td class="px-4 py-3">
                        @if($asset->assignedUser)
                        <div class="flex items-center gap-1.5">
                            <img src="{{ $asset->assignedUser->avatarUrl() }}" class="w-5 h-5 rounded-full" alt="">
                            <span class="text-xs text-gray-600 truncate max-w-[100px]">{{ $asset->assignedUser->name }}</span>
                        </div>
                        @else
                        <span class="text-xs text-gray-300 italic">Unassigned</span>
                        @endif
                    </td>

                    {{-- Warranty --}}
                    <td class="px-4 py-3">
                        @if($asset->warranty_expiry)
                            @if($asset->isWarrantyExpired())
                            <span class="text-xs text-red-500 font-medium">Expired</span>
                            @elseif($asset->isWarrantyExpiringSoon())
                            <span class="text-xs text-amber-600 font-medium" title="{{ $asset->warranty_expiry->format('M d, Y') }}">
                                Exp. {{ $asset->warranty_expiry->diffForHumans(null, true) }}
                            </span>
                            @else
                            <span class="text-xs text-gray-500">{{ $asset->warranty_expiry->format('M Y') }}</span>
                            @endif
                        @else
                        <span class="text-xs text-gray-300">—</span>
                        @endif
                    </td>

                    {{-- Location --}}
                    <td class="px-4 py-3">
                        <span class="text-xs text-gray-500">{{ $asset->location ?? '—' }}</span>
                    </td>

                    {{-- Arrow --}}
                    <td class="px-4 py-3">
                        <svg class="w-3.5 h-3.5 text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($assets->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">
            {{ $assets->links() }}
        </div>
        @endif
        @endif
    </div>
</div>
@endsection