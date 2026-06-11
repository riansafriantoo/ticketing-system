@extends('layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', 'Overview of all service desk activity')

@section('content')
<div class="space-y-6">

    {{-- ── Metric cards ─────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-blue-400 mb-1">Total tickets</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $metricsRequester['total_tickets'] }}</p>
            <p class="text-xs text-gray-400 mt-1">All tickets in system</p>
        </div>
        {{-- <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-gray-400 mb-1">Open tickets</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $metricsRequester['total_open'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Active in system</p>
        </div> --}}
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-yellow-600 mb-1">In Progress</p>
            <p class="text-2xl font-semibold text-yellow-600">{{ $metricsRequester['in_progress'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Tickets In Progress</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-green-600 mb-1">Closed</p>
            <p class="text-2xl font-semibold text-green-600">{{ $metricsRequester['resolved'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Tickets Closed</p>
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