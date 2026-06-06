@extends('layouts.app')

@section('title', 'Tickets')
@section('heading', 'Tickets')
@section('subheading', $tickets->total() . ' total')

@section('header-actions')
@if(auth()->user()->isAgent() && $metrics)
<div class="flex items-center gap-4 mr-2">
    <div class="text-center">
        <p class="text-xs text-gray-400">Open</p>
        <p class="text-sm font-semibold text-gray-900">{{ $metrics['total_open'] }}</p>
    </div>
    <div class="text-center">
        <p class="text-xs text-red-400">Overdue</p>
        <p class="text-sm font-semibold text-red-600">{{ $metrics['overdue'] }}</p>
    </div>
    <div class="text-center">
        <p class="text-xs text-gray-400">Resolved today</p>
        <p class="text-sm font-semibold text-green-600">{{ $metrics['resolved_today'] }}</p>
    </div>
</div>
@endif
@endsection

@section('content')
<div class="space-y-4">

    {{-- ── Filters ─────────────────────────────────────────────────────────── --}}
    <form method="GET" class="flex flex-wrap items-center gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search tickets…"
               class="w-56 text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200">

        <select name="status" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:border-brand-400 bg-white">
            <option value="">All statuses</option>
            @foreach($statuses as $s)
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

        <select name="category" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:border-brand-400 bg-white">
            <option value="">All categories</option>
            @foreach($categories as $c)
            <option value="{{ $c->value }}" {{ request('category') === $c->value ? 'selected' : '' }}>
                {{ $c->label() }}
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

        <label class="flex items-center gap-1.5 text-sm text-gray-600 cursor-pointer select-none">
            <input type="checkbox" name="overdue" value="1" {{ request('overdue') ? 'checked' : '' }}
                   class="rounded border-gray-300 text-brand-600 focus:ring-brand-300">
            Overdue only
        </label>

        <button type="submit"
                class="px-3 py-1.5 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
            Filter
        </button>

        @if(request()->hasAny(['search','status','priority','category','assignee','overdue']))
        <a href="{{ route('tickets.index') }}" class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg">
            Clear
        </a>
        @endif
    </form>

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
                    <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-36">Assignee</th>
                    @endif
                    <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-36">SLA</th>
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
                            @if($ticket->isOverdue())
                            <span title="SLA Breached" class="mt-0.5 flex-shrink-0 w-1.5 h-1.5 rounded-full bg-red-500"></span>
                            @endif
                            <div>
                                <p class="text-sm font-medium text-gray-900 line-clamp-1">{{ $ticket->subject }}</p>
                                <p class="text-xs text-gray-400 capitalize">{{ $ticket->category->label() }}</p>
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
                    @endif
                    <td class="px-4 py-3">
                        @if($ticket->sla_due_at && !$ticket->status->isTerminal())
                            @if($ticket->isOverdue())
                            <span class="text-xs font-medium text-red-600">Overdue</span>
                            @else
                            <span class="text-xs text-gray-500">{{ $ticket->sla_due_at->diffForHumans() }}</span>
                            @endif
                        @else
                        <span class="text-xs text-gray-300">—</span>
                        @endif
                    </td>
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