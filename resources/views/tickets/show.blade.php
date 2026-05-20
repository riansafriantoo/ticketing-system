@extends('layouts.app')

@section('title', $ticket->ticketNumber())
@section('heading', $ticket->ticketNumber())
@section('subheading', $ticket->subject)

@section('content')
<div class="flex gap-6 items-start">

    {{-- ── Main column ──────────────────────────────────────────────────────── --}}
    <div class="flex-1 min-w-0 space-y-4">

        {{-- Ticket body --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <div class="flex items-center gap-2 mb-4">
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-medium badge-{{ $ticket->status->value }}">
                    {{ $ticket->status->label() }}
                </span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-medium badge-{{ $ticket->priority->value }}">
                    {{ $ticket->priority->label() }}
                </span>
                <span class="text-xs text-gray-400 capitalize">{{ $ticket->category->label() }}</span>
                @if($ticket->isOverdue())
                <span class="ml-auto inline-flex items-center gap-1 px-2 py-0.5 bg-red-50 text-red-600 rounded-md text-[11px] font-medium">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    SLA Breached
                </span>
                @endif
            </div>

            <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ $ticket->subject }}</h2>
            <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $ticket->description }}</div>

            {{-- Attachments --}}
            @if($ticket->attachments->isNotEmpty())
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">Attachments</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($ticket->attachments as $attachment)
                    <a href="{{ $attachment->url() }}" target="_blank"
                       class="flex items-center gap-1.5 px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-700 hover:border-brand-300 hover:text-brand-600">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        {{ $attachment->original_name }}
                        <span class="text-gray-400">{{ $attachment->humanSize() }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- ── Comments ──────────────────────────────────────────────────────── --}}
        <div class="space-y-3">
            @foreach($ticket->comments as $comment)
            @if($comment->is_internal && !auth()->user()->isAgent())
                @continue
            @endif
            <div class="bg-white rounded-xl border {{ $comment->is_internal ? 'border-amber-100 bg-amber-50' : 'border-gray-100' }} p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <img src="{{ $comment->user->avatarUrl() }}" class="w-6 h-6 rounded-full" alt="">
                        <span class="text-xs font-medium text-gray-700">{{ $comment->user->name }}</span>
                        <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                        @if($comment->is_internal)
                        <span class="px-1.5 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-medium rounded">Internal Note</span>
                        @endif
                    </div>
                    @can('delete', $comment)
                    <form method="POST" action="{{ route('tickets.comments.destroy', [$ticket, $comment]) }}"
                          onsubmit="return confirm('Delete this comment?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-gray-300 hover:text-red-400">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                    @endcan
                </div>
                <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $comment->body }}</p>
            </div>
            @endforeach
        </div>

        {{-- ── Add comment ──────────────────────────────────────────────────── --}}
        @if(!$ticket->status->isTerminal())
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <form method="POST" action="{{ route('tickets.comments.store', $ticket) }}" class="space-y-3">
                @csrf
                <textarea name="body" rows="3" placeholder="Add a reply…"
                          class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200 resize-none @error('body') border-red-400 @enderror">{{ old('body') }}</textarea>
                @error('body')
                <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        @if(auth()->user()->isAgent())
                        <label class="flex items-center gap-1.5 text-xs text-gray-600 cursor-pointer select-none">
                            <input type="checkbox" name="is_internal" value="1"
                                   class="rounded border-gray-300 text-amber-500 focus:ring-amber-300">
                            Internal note (agents only)
                        </label>
                        @endif
                    </div>
                    <button type="submit"
                            class="px-3 py-1.5 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                        Post Reply
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>

    {{-- ── Sidebar ───────────────────────────────────────────────────────────── --}}
    <aside class="w-72 flex-shrink-0 space-y-4">

        {{-- Ticket meta --}}
        <div class="bg-white rounded-xl border border-gray-100 p-4 space-y-3">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Details</p>

            <div class="space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-gray-400">Requester</span>
                    <div class="flex items-center gap-1">
                        <img src="{{ $ticket->requester->avatarUrl() }}" class="w-4 h-4 rounded-full" alt="">
                        <span class="text-gray-700">{{ $ticket->requester->name }}</span>
                    </div>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Opened</span>
                    <span class="text-gray-700">{{ $ticket->created_at->format('M d, Y H:i') }}</span>
                </div>
                @if($ticket->sla_due_at)
                <div class="flex justify-between">
                    <span class="text-gray-400">SLA due</span>
                    <span class="{{ $ticket->isOverdue() ? 'text-red-600 font-medium' : 'text-gray-700' }}">
                        {{ $ticket->sla_due_at->format('M d, Y H:i') }}
                    </span>
                </div>
                @endif
                @if($ticket->resolved_at)
                <div class="flex justify-between">
                    <span class="text-gray-400">Resolved</span>
                    <span class="text-gray-700">{{ $ticket->resolved_at->format('M d, Y') }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Status transition --}}
        @if($ticket->status->transitions() && !$ticket->status->isTerminal())
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-3">Change Status</p>
            <div class="space-y-1.5">
                @foreach($ticket->status->transitions() as $nextStatus)
                <form method="POST" action="{{ route('tickets.transition', $ticket) }}">
                    @csrf
                    <input type="hidden" name="status" value="{{ $nextStatus->value }}">
                    <button type="submit"
                            class="w-full text-left px-3 py-2 rounded-lg text-xs font-medium border hover:border-gray-300 hover:bg-gray-50 transition-colors badge-{{ $nextStatus->value }} border-transparent">
                        → {{ $nextStatus->label() }}
                    </button>
                </form>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Assign (agents only) --}}
        @can('assign', $ticket)
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-3">Assignee</p>
            <form method="POST" action="{{ route('tickets.assign', $ticket) }}" class="space-y-2">
                @csrf
                <select name="assignee_id"
                        class="w-full text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 focus:outline-none focus:border-brand-400 bg-white">
                    <option value="">Unassigned</option>
                    @foreach($agents as $agent)
                    <option value="{{ $agent->id }}" {{ $ticket->assignee_id == $agent->id ? 'selected' : '' }}>
                        {{ $agent->name }}
                    </option>
                    @endforeach
                </select>
                <button type="submit"
                        class="w-full px-3 py-1.5 text-xs font-medium text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50">
                    Update Assignee
                </button>
            </form>
        </div>
        @endcan

        {{-- Activity log --}}
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-3">Activity</p>
            <div class="space-y-3">
                @forelse($ticket->activities->take(10) as $activity)
                <div class="flex gap-2">
                    <div class="flex-shrink-0 mt-0.5">
                        <div class="w-1.5 h-1.5 rounded-full bg-gray-200 mt-1"></div>
                    </div>
                    <div>
                        <p class="text-xs text-gray-600 leading-relaxed">{!! $activity->description() !!}</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">{{ $activity->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <p class="text-xs text-gray-400">No activity yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Admin: Edit / Delete --}}
        @if(auth()->user()->isAdmin())
        <div class="bg-white rounded-xl border border-gray-100 p-4 space-y-2">
            <a href="{{ route('tickets.edit', $ticket) }}"
               class="flex items-center justify-center gap-1.5 w-full px-3 py-1.5 text-xs font-medium text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Ticket
            </a>
            <form method="POST" action="{{ route('tickets.destroy', $ticket) }}"
                  onsubmit="return confirm('Permanently delete this ticket?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="flex items-center justify-center gap-1.5 w-full px-3 py-1.5 text-xs font-medium text-red-600 border border-red-100 rounded-lg hover:bg-red-50">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete Ticket
                </button>
            </form>
        </div>
        @endif
    </aside>
</div>
@endsection