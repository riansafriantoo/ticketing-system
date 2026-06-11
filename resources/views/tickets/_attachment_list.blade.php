<div class="flex flex-wrap gap-2">
    @foreach($attachments as $attachment)
    <div class="group flex items-center gap-2 px-2.5 py-1.5 rounded-lg border border-gray-200 bg-gray-50 hover:border-gray-300 hover:bg-white transition-colors">

        {{-- File type icon / thumbnail --}}
        @if($attachment->isImage())
        <a href="{{ $attachment->url() }}" target="_blank" rel="noopener"
           class="flex-shrink-0">
            <img src="{{ $attachment->url() }}"
                 class="w-8 h-8 rounded object-cover border border-gray-100"
                 alt="{{ $attachment->original_name }}">
        </a>
        @else
        <a href="{{ $attachment->url() }}" target="_blank" rel="noopener"
           class="flex-shrink-0 w-8 h-8 rounded flex items-center justify-center {{ $attachment->badgeColor() }}">
            <span class="text-[9px] font-bold leading-none">{{ $attachment->extensionLabel() }}</span>
        </a>
        @endif

        {{-- File info --}}
        <div class="min-w-0">
            <a href="{{ $attachment->url() }}"
               target="_blank"
               rel="noopener"
               class="text-xs font-medium text-gray-700 hover:text-brand-600 truncate block max-w-[140px]"
               title="{{ $attachment->original_name }}">
                {{ Str::limit($attachment->original_name, 22) }}
            </a>
            <p class="text-[10px] text-gray-400">{{ $attachment->humanSize() }}</p>
        </div>

        {{-- Download button (always visible) --}}
        <a href="{{ $attachment->url() }}"
           download="{{ $attachment->original_name }}"
           class="flex-shrink-0 p-1 rounded text-gray-400 hover:text-brand-600 hover:bg-brand-50 transition-colors"
           title="Download" onclick="event.stopPropagation()">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
        </a>

        {{-- Delete button (comment attachments only, for owner/agent/admin) --}}
        @if($comment !== null)
            @if(auth()->id() === $attachment->user_id || auth()->user()->isAgent())
            <form method="POST"
                  action="{{ route('tickets.comments.attachments.destroy', [$ticket, $comment, $attachment]) }}"
                  onsubmit="return confirm('Remove this attachment?')"
                  class="flex-shrink-0">
                @csrf @method('DELETE')
                <button type="submit"
                        class="p-1 rounded text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors opacity-0 group-hover:opacity-100"
                        title="Remove attachment">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </form>
            @endif
        @endif
    </div>
    @endforeach
</div>
