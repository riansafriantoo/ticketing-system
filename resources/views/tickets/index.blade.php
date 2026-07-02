@extends('layouts.app')

@section('title', 'Tickets')
@section('heading', 'Tickets')
@section('subheading', $tickets->total() . ' total')

@section('header-actions')
@if(auth()->user()->isAgent() && $metrics)
<div class="flex items-center gap-5 mr-2">
    <div class="text-center">
        <p class="text-xs text-gray-400">Open</p>
        <p class="text-sm font-semibold text-gray-900">{{ $metrics['total_tickets'] }}</p>
    </div>
    <div class="text-center">
        <p class="text-xs text-gray-400">In Progress</p>
        <p class="text-sm font-semibold text-yellow-600">{{ $metrics['in_progress'] }}</p>
    </div>
    <div class="text-center">
        <p class="text-xs text-gray-400">Closed</p>
        <p class="text-sm font-semibold text-green-600">{{ $metrics['resolved_today'] }}</p>
    </div>
    <button type="button" onclick="openExportModal()"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    Export
    </button>
</div>
@endif
@endsection

@section('content')
<div class="space-y-4">
    {{-- ── Filters ─────────────────────────────────────────────────────────── --}}
    <form method="GET" class="flex flex-wrap items-center gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search tickets/subject…"
               class="w-56 text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200">

        <select name="status" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:border-brand-400 bg-white">
            <option value="">All statuses</option>
            @foreach($statusesNew as $s)
            <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>
                {{ $s->label() }}
            </option>
            @endforeach
        </select>

        <select name="priority" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:border-brand-400 bg-white">
            <option value="">All priorities</option>
            @foreach($priorities as $p)
            <option value="{{ $p->value }}" {{ request('priority') === $p->value ? 'selected' : '' }}>
                {{ $p->label() }}
            </option>
            @endforeach
        </select>

        <select name="requester" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:border-brand-400 bg-white">
            <option value="">All requesters</option>
            @foreach($requesters as $requester)
            <option value="{{ $requester->id }}" {{ request('requester') == $requester->id ? 'selected' : '' }}>
                {{ $requester->name }}
            </option>
            @endforeach
        </select>

        @if(auth()->user()->isAgent())
        <select name="assignee" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:border-brand-400 bg-white">
            <option value="">All agents</option>
            @foreach($agents as $agent)
            <option value="{{ $agent->id }}" {{ request('assignee') == $agent->id ? 'selected' : '' }}>
                {{ $agent->name }}
            </option>
            @endforeach
        </select>
        @endif

        <button type="submit"
                class="px-3 py-1.5 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
            Filter
        </button>

        @if(request()->hasAny(['search','status','priority','assignee','overdue']))
        <a href="{{ route('tickets.index') }}" class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg">
            Clear
        </a>
        @endif
    </form>

    @include('tickets._export_modal', ['agents' => $agents ?? collect()])
    <div id="export-modal-backdrop" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4" onclick="closeExportModal(event)">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden" onclick="event.stopPropagation()">
            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-brand-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h2 class="text-sm font-semibold text-gray-900">Export Tickets</h2>
                </div>
                <button onclick="closeExportModal()" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Form — plain GET so the browser navigates straight to the
                file download, no JS/fetch needed for the actual export --}}
            <form method="GET" action="{{ route('tickets.export') }}" class="p-5 space-y-4">

                {{-- Date range — the primary filter for this feature --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                        Date Range
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] text-gray-400 mb-1">From</label>
                            <input type="date" name="date_from"
                                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200">
                        </div>
                        <div>
                            <label class="block text-[11px] text-gray-400 mb-1">To</label>
                            <input type="date" name="date_to"
                                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200">
                        </div>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1.5">Leave both blank to export all tickets.</p>
                </div>

                {{-- Quick-pick ranges — fills the two date fields above --}}
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" onclick="setExportRange(7)"
                            class="px-2.5 py-1 text-[11px] font-medium text-gray-600 border border-gray-200 rounded-full hover:border-brand-300 hover:text-brand-600">
                        Last 7 days
                    </button>
                    <button type="button" onclick="setExportRange(30)"
                            class="px-2.5 py-1 text-[11px] font-medium text-gray-600 border border-gray-200 rounded-full hover:border-brand-300 hover:text-brand-600">
                        Last 30 days
                    </button>
                    <button type="button" onclick="setExportRange('month')"
                            class="px-2.5 py-1 text-[11px] font-medium text-gray-600 border border-gray-200 rounded-full hover:border-brand-300 hover:text-brand-600">
                        This month
                    </button>
                    <button type="button" onclick="clearExportRange()"
                            class="px-2.5 py-1 text-[11px] font-medium text-gray-400 hover:text-gray-600">
                        Clear
                    </button>
                </div>

                {{-- Secondary filters --}}
                <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                    <div>
                        <label class="block text-[11px] text-gray-400 mb-1">Status</label>
                        <select name="status" class="w-full text-sm border border-gray-200 rounded-lg px-2.5 py-2 bg-white focus:outline-none focus:border-brand-400">
                            <option value="">All</option>
                            @foreach(\App\Enums\TicketStatusNew::cases() as $s)
                            <option value="{{ $s->value }}">{{ $s->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] text-gray-400 mb-1">Priority</label>
                        <select name="priority" class="w-full text-sm border border-gray-200 rounded-lg px-2.5 py-2 bg-white focus:outline-none focus:border-brand-400">
                            <option value="">All</option>
                            @foreach(\App\Enums\TicketPriority::cases() as $p)
                            <option value="{{ $p->value }}">{{ $p->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] text-gray-400 mb-1">Category</label>
                        <select name="category" class="w-full text-sm border border-gray-200 rounded-lg px-2.5 py-2 bg-white focus:outline-none focus:border-brand-400">
                            <option value="">All</option>
                            @foreach(\App\Enums\TicketCategory::cases() as $c)
                            <option value="{{ $c->value }}">{{ $c->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] text-gray-400 mb-1">Requester</label>
                        <select name="requester" class="w-full text-sm border border-gray-200 rounded-lg px-2.5 py-2 bg-white focus:outline-none focus:border-brand-400">
                            <option value="">All</option>
                            @foreach($requesters as $requester)
                            <option value="{{ $requester->id }}">{{ $requester->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($agents->isNotEmpty())
                    <div>
                        <label class="block text-[11px] text-gray-400 mb-1">Agent</label>
                        <select name="assignee" class="w-full text-sm border border-gray-200 rounded-lg px-2.5 py-2 bg-white focus:outline-none focus:border-brand-400">
                            <option value="">All</option>
                            @foreach($agents as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>

                {{-- Footer actions --}}
                <div class="flex items-center justify-between pt-3">
                    <button type="button" onclick="closeExportModal()"
                            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
                        Cancel
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Download Excel
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Table ───────────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        @if($tickets->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <p class="text-sm font-medium text-gray-500">No tickets found</p>
            <p class="text-xs text-gray-400 mt-1">Try adjusting your filters or <a href="{{ route('tickets.create') }}" class="text-brand-600 underline">create a new ticket</a></p>
        </div>
        @else
        <table class="min-w-full">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-28">ID</th>
                    <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Subject</th>
                    <th class="text-left px-2 py-2 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-28">Status</th>
                    <th class="text-left px-2 py-2 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-24">Priority</th>
                    @if(auth()->user()->isAgent())
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-36">Requester</th>
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-36">Agents</th>
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-36">Work Duration</th>
                    @endif
                    <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-28">Created Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($tickets as $ticket)
                <tr class="ticket-row cursor-pointer" onclick="window.location='{{ route('tickets.show', $ticket) }}'">
                    <td class="px-4 py-3">
                        <span class="font-mono text-xs text-gray-500">{{ $ticket->ticketNumber() }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-start gap-2">
                            <div>
                                <p class="text-sm font-medium text-gray-900 line-clamp-1">{{ $ticket->subject }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-2 py-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium badge-{{ $ticket->status->value }}">
                            {{ $ticket->status->label() }}
                        </span>
                    </td>
                    <td class="px-2 py-2">
                        <span class="inline-flex items-left px-2 py-0.5 rounded-md text-[11px] font-medium badge-{{ $ticket->priority->value }}">
                            {{ $ticket->priority->label() }}
                        </span>
                    </td>
                    @if(auth()->user()->isAgent())
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1.5">
                                <img src="{{ $ticket->requester->avatarUrl() }}" class="w-5 h-5 rounded-full" alt="">
                                <span class="text-xs text-gray-600 truncate max-w-[100px]">{{ $ticket->requester->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if($ticket->assignee)
                            <div class="flex items-center gap-1.5">
                                <img src="{{ $ticket->assignee->avatarUrl() }}" class="w-5 h-5 rounded-full" alt="">
                                <span class="text-xs text-gray-600 truncate max-w-[100px]">{{ $ticket->assignee->name }}</span>
                            </div>
                            @else
                            <span class="text-xs text-gray-300 italic">Unassigned</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($ticket->closed_at)
                                <span class="text-xs font-medium text-gray-600">{{ $ticket->created_at->diffForHumans($ticket->closed_at, true) }}</span>
                            @else
                                <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>
                    @endif
                    <td class="px-4 py-3">
                        <span class="text-xs text-gray-400">{{ $ticket->created_at }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

            {{-- Pagination --}}
            @if($tickets->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $tickets->links('vendor.pagination.simple-tailwind') }}
            </div>
            @endif
        @endif
    </div>
</div>
@endsection

<script>
function openExportModal() {
    document.getElementById('export-modal-backdrop').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeExportModal(event) {
    if (event && event.target !== document.getElementById('export-modal-backdrop')) return;
    document.getElementById('export-modal-backdrop').classList.add('hidden');
    document.body.style.overflow = '';
}

function setExportRange(range) {
    const fromInput = document.querySelector('#export-modal-backdrop input[name="date_from"]');
    const toInput   = document.querySelector('#export-modal-backdrop input[name="date_to"]');
    const today     = new Date();
    const fmt = d => d.toISOString().split('T')[0];

    toInput.value = fmt(today);

    if (range === 'month') {
        const firstOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        fromInput.value = fmt(firstOfMonth);
        return;
    }

    const past = new Date(today);
    past.setDate(past.getDate() - range);
    fromInput.value = fmt(past);
}

function clearExportRange() {
    document.querySelector('#export-modal-backdrop input[name="date_from"]').value = '';
    document.querySelector('#export-modal-backdrop input[name="date_to"]').value = '';
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeExportModal();
});
</script>