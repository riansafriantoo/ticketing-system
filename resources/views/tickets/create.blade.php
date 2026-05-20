@extends('layouts.app')

@section('title', 'New Ticket')
@section('heading', 'New Ticket')
@section('subheading', 'Describe your issue and we\'ll get back to you')

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        {{-- Subject --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Subject <span class="text-red-500">*</span></label>
                <input type="text" name="subject" value="{{ old('subject') }}"
                       placeholder="Brief summary of the issue…"
                       class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200 @error('subject') border-red-400 @enderror">
                @error('subject')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Description <span class="text-red-500">*</span></label>
                <textarea name="description" rows="6"
                          placeholder="Provide as much detail as possible — steps to reproduce, error messages, screenshots…"
                          class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200 resize-none @error('description') border-red-400 @enderror">{{ old('description') }}</textarea>
                @error('description')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Classification --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Classification</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Category <span class="text-red-500">*</span></label>
                    <select name="category"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 bg-white @error('category') border-red-400 @enderror">
                        <option value="">Select category…</option>
                        @foreach($categories as $c)
                        <option value="{{ $c->value }}" {{ old('category') === $c->value ? 'selected' : '' }}>
                            {{ $c->label() }}
                        </option>
                        @endforeach
                    </select>
                    @error('category')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Priority <span class="text-red-500">*</span></label>
                    <select name="priority"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 bg-white @error('priority') border-red-400 @enderror">
                        @foreach($priorities as $p)
                        <option value="{{ $p->value }}" {{ old('priority', 'medium') === $p->value ? 'selected' : '' }}>
                            {{ $p->label() }} (SLA {{ $p->slaHours() }}h)
                        </option>
                        @endforeach
                    </select>
                    @error('priority')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Attachments --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Attachments <span class="text-gray-300 font-normal normal-case tracking-normal">optional, up to 5 files × 10 MB</span></h3>
            <label class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-gray-200 rounded-lg cursor-pointer hover:border-brand-300 hover:bg-brand-50 transition-colors">
                <svg class="w-6 h-6 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                <p class="text-xs text-gray-400">Click to attach files or drag and drop</p>
                <input type="file" name="attachments[]" multiple class="hidden"
                       accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip">
            </label>
            @error('attachments.*')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Submit --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('tickets.index') }}" class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit"
                    class="px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                Submit Ticket
            </button>
        </div>
    </form>
</div>
@endsection