@extends('layouts.app')

@section('title', $user->name)
@section('heading', $user->name)
@section('subheading', $user->email)

@section('header-actions')
<a href="{{ route('admin.users.edit', $user) }}"
   class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
    </svg>
    Edit User
</a>
@endsection

@section('content')
<div class="flex gap-6 items-start">

    {{-- ── Left column ──────────────────────────────────────────────────────── --}}
    <div class="flex-1 min-w-0 space-y-4">

        {{-- Recent tickets --}}
        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Recent Tickets Submitted</h3>
                <a href="{{ route('tickets.index', ['requester' => $user->id]) }}"
                   class="text-xs text-brand-600 hover:underline">View all →</a>
            </div>
            @if($recentTickets->isEmpty())
            <div class="px-5 py-10 text-center">
                <p class="text-sm text-gray-400">No tickets submitted yet.</p>
            </div>
            @else
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-50 bg-gray-50">
                        <th class="text-left px-5 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Ticket</th>
                        <th class="text-left px-4 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-28">Status</th>
                        <th class="text-left px-4 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-36">Assignee</th>
                        <th class="text-left px-4 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-28">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($recentTickets as $ticket)
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('tickets.show', $ticket) }}'">
                        <td class="px-5 py-3">
                            <p class="text-sm font-medium text-gray-800">{{ $ticket->subject }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $ticket->ticketNumber() }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-md text-[11px] font-medium badge-{{ $ticket->status->value }}">
                                {{ $ticket->status->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($ticket->assignee)
                            <div class="flex items-center gap-1.5">
                                <img src="{{ $ticket->assignee->avatarUrl() }}" class="w-4 h-4 rounded-full" alt="">
                                <span class="text-xs text-gray-600">{{ $ticket->assignee->name }}</span>
                            </div>
                            @else
                            <span class="text-xs text-gray-300 italic">Unassigned</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs text-gray-400">{{ $ticket->created_at->format('M d, Y') }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    {{-- ── Right sidebar ─────────────────────────────────────────────────────── --}}
    <aside class="w-64 flex-shrink-0 space-y-4">

        {{-- Avatar card --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5 text-center">
            <img src="{{ $user->avatarUrl() }}" class="w-16 h-16 rounded-full mx-auto mb-3" alt="">
            <p class="text-sm font-semibold text-gray-900">{{ $user->name }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $user->email }}</p>
            <div class="mt-2 flex items-center justify-center gap-2">
                <span class="inline-flex px-2 py-0.5 rounded-md text-[11px] font-semibold capitalize
                    @if($user->hasRole('admin')) bg-purple-100 text-purple-800
                    @elseif($user->hasRole('agent')) bg-blue-100 text-blue-800
                    @else bg-gray-100 text-gray-600 @endif">
                    {{ $user->roles->first()?->name ?? 'user' }}
                </span>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium
                    {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-green-500' : 'bg-red-400' }}"></span>
                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>

        {{-- Stats --}}
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-3">Activity</p>
            <div class="space-y-2">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Tickets submitted</span>
                    <span class="font-semibold text-gray-900">{{ $user->submitted_tickets_count }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Tickets assigned</span>
                    <span class="font-semibold text-gray-900">{{ $user->assigned_tickets_count }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Member since</span>
                    <span class="text-gray-700">{{ $user->created_at->format('M Y') }}</span>
                </div>
                @if($user->department)
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Department</span>
                    <span class="text-gray-700">{{ $user->department }}</span>
                </div>
                @endif
                @if($user->phone)
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Phone</span>
                    <span class="text-gray-700">{{ $user->phone }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div class="bg-white rounded-xl border border-gray-100 p-4 space-y-2">
            <a href="{{ route('admin.users.edit', $user) }}"
               class="flex items-center justify-center gap-1.5 w-full px-3 py-2 text-xs font-medium text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit User
            </a>

            @if($user->id !== auth()->id())
            <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                @csrf @method('PATCH')
                <button type="submit"
                        class="flex items-center justify-center gap-1.5 w-full px-3 py-2 text-xs font-medium rounded-lg border transition-colors
                        {{ $user->is_active
                            ? 'text-amber-700 border-amber-200 hover:bg-amber-50'
                            : 'text-green-700 border-green-200 hover:bg-green-50' }}">
                    @if($user->is_active)
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                    Deactivate Account
                    @else
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Activate Account
                    @endif
                </button>
            </form>

            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                  onsubmit="return confirm('Permanently delete {{ addslashes($user->name) }}?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="flex items-center justify-center gap-1.5 w-full px-3 py-2 text-xs font-medium text-red-600 border border-red-100 rounded-lg hover:bg-red-50">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete User
                </button>
            </form>
            @endif
        </div>
    </aside>
</div>
@endsection