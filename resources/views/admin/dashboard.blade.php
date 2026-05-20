@extends('layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', 'Overview of all service desk activity')

@section('content')
<div class="space-y-6">

    {{-- ── Metric cards ─────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-gray-400 mb-1">Open tickets</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $metrics['total_open'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Active in system</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-red-400 mb-1">SLA breached</p>
            <p class="text-2xl font-semibold {{ $metrics['overdue'] > 0 ? 'text-red-600' : 'text-gray-900' }}">
                {{ $metrics['overdue'] }}
            </p>
            <p class="text-xs text-gray-400 mt-1">Require immediate attention</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-green-600 mb-1">Resolved today</p>
            <p class="text-2xl font-semibold text-green-600">{{ $metrics['resolved_today'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Tickets closed</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-gray-400 mb-1">Avg resolution</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $metrics['avg_resolution'] }}<span class="text-sm font-normal text-gray-400">h</span></p>
            <p class="text-xs text-gray-400 mt-1">All time average</p>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4">

        {{-- ── Tickets by status ────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">By Status</h3>
            @php
                $statusColors = ['open'=>'#4f6ef7','in_progress'=>'#f59e0b','on_hold'=>'#9ca3af','resolved'=>'#22c55e','closed'=>'#94a3b8'];
                $total = $byStatus->sum();
            @endphp
            <div class="space-y-2">
                @foreach($byStatus as $status => $count)
                @php $pct = $total > 0 ? round($count / $total * 100) : 0; @endphp
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="capitalize text-gray-600">{{ str_replace('_', ' ', $status) }}</span>
                        <span class="text-gray-900 font-medium">{{ $count }}</span>
                    </div>
                    <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full" style="width: {{ $pct }}%; background: {{ $statusColors[$status] ?? '#94a3b8' }}"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ── By priority ──────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Open by Priority</h3>
            @php
                $prioColors = ['low'=>'#22c55e','medium'=>'#4f6ef7','high'=>'#f59e0b','critical'=>'#ef4444'];
                $prioTotal  = $byPriority->sum() ?: 1;
            @endphp
            <div class="space-y-2">
                @foreach($byPriority as $priority => $count)
                @php $pct = round($count / $prioTotal * 100); @endphp
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="capitalize text-gray-600">{{ $priority }}</span>
                        <span class="text-gray-900 font-medium">{{ $count }}</span>
                    </div>
                    <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full" style="width: {{ $pct }}%; background: {{ $prioColors[$priority] ?? '#94a3b8' }}"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ── Agent workload ────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Agent Workload</h3>
            @php $maxLoad = $agentLoad->max('open_count') ?: 1; @endphp
            <div class="space-y-2">
                @foreach($agentLoad as $agent)
                <div class="flex items-center gap-2">
                    <img src="{{ $agent->avatarUrl() }}" class="w-5 h-5 rounded-full flex-shrink-0" alt="">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-gray-700 truncate">{{ $agent->name }}</p>
                        <div class="h-1 bg-gray-100 rounded-full overflow-hidden mt-0.5">
                            <div class="h-full bg-brand-500 rounded-full"
                                 style="width: {{ $agent->open_count / $maxLoad * 100 }}%"></div>
                        </div>
                    </div>
                    <span class="text-xs text-gray-500 flex-shrink-0">{{ $agent->open_count }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Recent tickets ────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Recent Tickets</h3>
            <a href="{{ route('tickets.index') }}" class="text-xs text-brand-600 hover:underline">View all →</a>
        </div>
        <table class="min-w-full">
            <tbody class="divide-y divide-gray-50">
                @foreach($recentTickets as $ticket)
                <tr class="ticket-row cursor-pointer" onclick="window.location='{{ route('tickets.show', $ticket) }}'">
                    <td class="px-4 py-3 w-28">
                        <span class="font-mono text-xs text-gray-500">{{ $ticket->ticketNumber() }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-sm text-gray-800 line-clamp-1">{{ $ticket->subject }}</p>
                    </td>
                    <td class="px-4 py-3 w-28">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium badge-{{ $ticket->status->value }}">
                            {{ $ticket->status->label() }}
                        </span>
                    </td>
                    <td class="px-4 py-3 w-36">
                        @if($ticket->assignee)
                        <div class="flex items-center gap-1.5">
                            <img src="{{ $ticket->assignee->avatarUrl() }}" class="w-4 h-4 rounded-full" alt="">
                            <span class="text-xs text-gray-500">{{ $ticket->assignee->name }}</span>
                        </div>
                        @else
                        <span class="text-xs text-gray-300 italic">Unassigned</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 w-28">
                        <span class="text-xs text-gray-400">{{ $ticket->created_at->diffForHumans(null, true) }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection