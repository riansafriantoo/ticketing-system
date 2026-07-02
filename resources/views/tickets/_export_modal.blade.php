{{--
    ════════════════════════════════════════════════════════════════════
    EXPORT MODAL
    Include this partial in resources/views/tickets/index.blade.php,
    anywhere inside the page body (it renders as a hidden overlay until
    toggled). Also add the trigger button shown in the comment block
    below, near your existing "New Ticket" button in the header.
    ════════════════════════════════════════════════════════════════════

    TRIGGER BUTTON — add this next to your "New Ticket" button:

    <button type="button" onclick="openExportModal()"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Export
    </button>

    INCLUDE THIS PARTIAL — add anywhere in index.blade.php, e.g. right
    after the closing </div> of your filters row:

    @include('tickets._export_modal', ['agents' => $agents ?? collect()])
--}}

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
        {{-- Form --}}
        <form method="GET" action="{{ route('tickets.export') }}" class="p-5 space-y-4">
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