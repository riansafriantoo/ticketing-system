@extends('layouts.app')
@section('title', 'Edit ' . $ticket->ticketNumber())
@section('heading', 'Edit Ticket')
@section('subheading', $ticket->ticketNumber() . ' — ' . $ticket->subject)

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="space-y-5">
        @csrf @method('PATCH')

        <div class="bg-white rounded-xl border border-gray-100 p-5 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Subject</label>
                <input type="text" name="subject" value="{{ old('subject', $ticket->subject) }}"
                       class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200 @error('subject') border-red-400 @enderror">
                @error('subject')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea name="description" rows="8"
                          class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200 resize-none @error('description') border-red-400 @enderror">{{ old('description', $ticket->description) }}</textarea>
                @error('description')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Classification</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Case Type <span class="text-red-500">*</span></label>
                    <select name="case_type" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 bg-white @error('case_type') border-red-400 @enderror">
                        <option value="">Select Case Type…</option>
                        @foreach($caseTypes as $c)
                        <option value="{{ $c->value }}" {{ old('case_type') === $c->value ? 'selected' : '' }}>
                            {{ $c->label() }}
                        </option>
                        @endforeach
                    </select>
                    @error('case_type')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Priority</label>
                    <select name="priority" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 bg-white">
                        @foreach($priorities as $p)
                        <option value="{{ $p->value }}" {{ old('priority', $ticket->priority->value) === $p->value ? 'selected' : '' }}>
                            {{ $p->label() }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('tickets.show', $ticket) }}" class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
                ← Back to Ticket
            </a>
            <button type="submit" class="px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection