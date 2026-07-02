@extends('layouts.app')
@section('title', $ticket->ticketNumber())
@section('heading', $ticket->ticketNumber())
@section('subheading', $ticket->subject)

@section('content')
<div class="flex gap-6 items-start">

    {{-- ══════════════════════════════════════════════════════════════════════
         MAIN COLUMN
    ══════════════════════════════════════════════════════════════════════ --}}
    <div class="flex-1 min-w-0 space-y-4">

        {{-- ── Ticket body ──────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <div class="flex items-center gap-2 mb-4 flex-wrap">
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold
                    @if($ticket->priority->value === 'critical') bg-red-100 text-red-800
                    @elseif($ticket->priority->value === 'high') bg-amber-100 text-amber-800
                    @elseif($ticket->priority->value === 'medium') bg-blue-100 text-blue-800
                    @else bg-green-100 text-green-800 @endif">
                    {{ $ticket->priority->label() }}
                </span>
                {{-- @if($ticket->isOverdue())
                <span class="ml-auto inline-flex items-center gap-1 px-2 py-0.5 bg-red-50 text-red-600 rounded-md text-[11px] font-medium">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    SLA Breached
                </span>
                @endif --}}
            </div>

            <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ $ticket->subject }}</h2>
            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $ticket->description }}</p>

            {{-- Ticket-level attachments (uploaded at creation) --}}
            @if($ticket->attachments->whereNull('comment_id')->isNotEmpty())
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Attachments</p>
                @include('tickets._attachment_list', [
                    'attachments' => $ticket->attachments->whereNull('comment_id'),
                    'comment'     => null,
                    'ticket'      => $ticket,
                ])
            </div>
            @endif
        </div>

        {{-- ── Comments ─────────────────────────────────────────────────────── --}}
        <div class="space-y-3">
            @foreach($ticket->comments as $comment)
                @if($comment->is_internal && !auth()->user()->isAgent())
                    @continue
                @endif

                <div class="bg-white rounded-xl border {{ $comment->is_internal ? 'border-amber-100 bg-amber-50/40' : 'border-gray-100' }} p-4">

                    {{-- Comment header --}}
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2 flex-wrap">
                            <img src="{{ $comment->user->avatarUrl() }}" class="w-6 h-6 rounded-full" alt="">
                            <span class="text-xs font-semibold text-gray-800">{{ $comment->user->name }}</span>
                            @if($comment->user->department)
                                <span class="text-[10px] text-gray-400">{{ $comment->user->department }}</span>
                            @endif
                            <span class="text-xs text-gray-400">·</span>
                            <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                            @if($comment->is_internal)
                            <span class="px-1.5 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-semibold rounded">
                                Internal note
                            </span>
                            @endif
                        </div>

                        @can('delete', $comment)
                        <form method="POST"
                              action="{{ route('tickets.comments.destroy', [$ticket, $comment]) }}"
                              onsubmit="return confirm('Delete this comment and its attachments?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="p-1 rounded text-gray-300 hover:text-red-400 hover:bg-red-50 transition-colors"
                                    title="Delete comment">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                        @endcan
                    </div>

                    {{-- Comment body --}}
                    <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $comment->body }}</p>

                    {{-- Comment attachments --}}
                    @if($comment->attachments->isNotEmpty())
                    <div class="mt-3 pt-3 border-t {{ $comment->is_internal ? 'border-amber-100' : 'border-gray-100' }}">
                        @include('tickets._attachment_list', [
                            'attachments' => $comment->attachments,
                            'comment'     => $comment,
                            'ticket'      => $ticket,
                        ])
                    </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- ══════════════════════════════════════════════════════════════════
             REPLY FORM WITH ATTACHMENT UPLOAD
        ══════════════════════════════════════════════════════════════════ --}}
        @if(!$ticket->status->isTerminal())
        <div class="bg-white rounded-xl border border-gray-100 p-5" id="reply-box">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Add Reply</h3>

            <form method="POST"
                  action="{{ route('tickets.comments.store', $ticket) }}"
                  enctype="multipart/form-data"
                  id="comment-form"
                  class="space-y-3">
                @csrf

                {{-- Body --}}
                <textarea name="body" id="comment-body" rows="4"
                          placeholder="Type your reply…"
                          class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200 resize-none
                                 @error('body') border-red-400 @enderror">{{ old('body') }}</textarea>
                @error('body')
                <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror

                {{-- ── Attachment panel ──────────────────────────────────────── --}}
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-3">

                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-medium text-gray-500 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            Attachments
                            <span class="text-gray-400 font-normal">· up to 5 files · 10 MB each</span>
                        </p>
                        <span id="c-file-count"
                              class="hidden text-[11px] font-semibold px-2 py-0.5 bg-brand-100 text-brand-700 rounded-full">
                            0 / 5
                        </span>
                    </div>

                    {{-- Hidden real inputs container (one <input> per file) --}}
                    <div id="c-inputs-container" class="hidden" aria-hidden="true"></div>

                    {{-- Trigger input --}}
                    <input type="file" id="c-file-trigger" multiple
                           accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip"
                           class="hidden"
                           onchange="cHandleSelect(event)">

                    {{-- Drop zone --}}
                    <div id="c-drop-zone"
                         class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-dashed border-gray-200 bg-white cursor-pointer
                                hover:border-brand-400 hover:bg-brand-50 transition-colors group"
                         onclick="document.getElementById('c-file-trigger').click()"
                         ondragover="cDragOver(event)"
                         ondragleave="cDragLeave(event)"
                         ondrop="cDrop(event)">
                        <div class="w-7 h-7 rounded-lg bg-gray-50 group-hover:bg-brand-100 flex items-center justify-center flex-shrink-0 transition-colors">
                            <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-brand-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-600 group-hover:text-brand-600 transition-colors">
                                Click to attach or drag &amp; drop
                            </p>
                            <p class="text-[11px] text-gray-400">jpg, png, gif, pdf, doc, docx, xls, xlsx, txt, zip</p>
                        </div>
                    </div>

                    {{-- Preview list --}}
                    <div id="c-preview-list" class="hidden mt-2 space-y-1.5"></div>

                    @error('attachments')   <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    @error('attachments.*') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Footer row --}}
                <div class="flex items-center justify-between pt-1">
                    {{-- <div class="flex items-center gap-3">
                        @if(auth()->user()->isAgent())
                        <label class="flex items-center gap-1.5 text-xs text-gray-600 cursor-pointer select-none">
                            <input type="checkbox" name="is_internal" value="1"
                                   class="rounded border-gray-300 text-amber-500 focus:ring-amber-300">
                            <span>Internal note</span>
                            <span class="text-[10px] text-gray-400">(agents only)</span>
                        </label>
                        @endif
                    </div> --}}
                    <button type="submit" id="comment-submit-btn"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                        </svg>
                        Post Reply
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         RIGHT SIDEBAR
    ══════════════════════════════════════════════════════════════════════ --}}
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
                    <span class="text-gray-400">Status</span>
                    <div class="flex items-center gap-1">
                        <span class="text-gray-700">{{ $ticket->status->label() }}</span>
                    </div>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Case Type</span>
                    <div class="flex items-center gap-1">
                        <span class="text-gray-700">{{ $ticket->case_type->label() }}</span>
                    </div>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Priority</span>
                    <div class="flex items-center gap-1">
                        <span class="text-gray-700"> {{ $ticket->priority->label() }}</span>
                    </div>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Opened</span>
                    <span class="text-gray-700">{{ $ticket->created_at->format('M d, Y H:i') }}</span>
                </div>
                @if($ticket->closed_at)
                <div class="flex justify-between">
                    <span class="text-gray-400">Closed</span>
                    <span class="text-gray-700">{{ $ticket->closed_at->format('M d, Y H:i') }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Status transition --}}
        @if(auth()->user()->isAgent())
            @if($ticket->status->transitions() && !$ticket->status->isTerminal())
            <div class="bg-white rounded-xl border border-gray-100 p-4">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-3">Change Status</p>
                <div class="relative">
                    <select id="status-dropdown" class="w-full appearance-none px-3 py-2 rounded-lg text-xs font-medium border border-gray-200 bg-white text-gray-700 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-300 transition-colors cursor-pointer pr-8" onchange="document.getElementById('status-form').querySelector('input[name=status]').value = this.value; document.getElementById('status-form').submit();">
                        <option value="" disabled selected>Pilih status...</option>
                        @foreach($ticket->status->transitions() as $nextStatus)
                        <option value="{{ $nextStatus->value }}">{{ $nextStatus->label() }}</option>
                        @endforeach
                    </select>
                    {{-- Chevron icon --}}
                    <div class="pointer-events-none absolute inset-y-0 right-2 flex items-center">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                {{-- Single hidden form --}}
                <form id="status-form" method="POST" action="{{ route('tickets.transition', $ticket) }}">
                    @csrf
                    <input type="hidden" name="status" value="">
                </form>
            </div>
            @endif
        @endif

        {{-- Assign (agents only) --}}
        @can('assign', $ticket)
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-3">Agent</p>
            <form method="POST" action="{{ route('tickets.assign', $ticket) }}" class="space-y-2">
                @csrf
                <select name="assignee_id" class="w-full text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 focus:outline-none focus:border-brand-400 bg-white">
                    <option value="">Unassigned</option>
                    @foreach($agents as $agent)
                    <option value="{{ $agent->id }}" {{ $ticket->assignee_id == $agent->id ? 'selected' : '' }}>
                        {{ $agent->name }}
                    </option>
                    @endforeach
                </select>
                <button type="submit" class="w-full px-3 py-1.5 text-xs font-medium text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50">
                    Update Agent
                </button>
            </form>
        </div>
        @endcan

        {{-- Activity log --}}
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-3">Activity</p>
            <div class="space-y-3 max-h-64 overflow-y-auto">
                @forelse($ticket->activities->take(15) as $activity)
                <div class="flex gap-2">
                    <div class="w-1.5 h-1.5 rounded-full bg-gray-200 mt-1.5 flex-shrink-0"></div>
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

        {{-- Admin actions --}}
        @if(auth()->user()->isAdmin())
        <div class="bg-white rounded-xl border border-gray-100 p-4 space-y-2">
            <a href="{{ route('tickets.edit', $ticket) }}"
               class="flex items-center justify-center gap-1.5 w-full px-3 py-2 text-xs font-medium text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Ticket
            </a>
            <form method="POST" action="{{ route('tickets.destroy', $ticket) }}"
                  onsubmit="return confirm('Permanently delete this ticket?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="flex items-center justify-center gap-1.5 w-full px-3 py-2 text-xs font-medium text-red-600 border border-red-100 rounded-lg hover:bg-red-50">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete Ticket
                </button>
            </form>
        </div>
        @endif
    </aside>
</div>
@endsection

@push('scripts')
<script>
// ─── Comment attachment state ─────────────────────────────────────────────────
const C_MAX       = 5;
const C_MAX_BYTES = 10 * 1024 * 1024;
const C_ALLOWED   = ['jpg','jpeg','png','gif','pdf','doc','docx','xls','xlsx','txt','zip'];

let cFiles  = [];   // { id, file, objectUrl }
let cNextId = 0;

// ─── Same per-file DataTransfer approach used in create.blade.php ─────────────
// One hidden <input type="file" name="attachments[]"> per file.
function cRebuildInputs() {
    const container = document.getElementById('c-inputs-container');
    container.innerHTML = '';

    cFiles.forEach(({ file }) => {
        const input = document.createElement('input');
        input.type  = 'file';
        input.name  = 'attachments[]';
        input.style.display = 'none';
        try {
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
        } catch (e) {
            console.warn('DataTransfer not supported for:', file.name);
        }
        container.appendChild(input);
    });

    container.classList.toggle('hidden', cFiles.length === 0);
}

// ─── Add files ────────────────────────────────────────────────────────────────
function cAddFiles(list) {
    const errors = [];
    Array.from(list).forEach(file => {
        if (cFiles.length >= C_MAX)            { errors.push(`Max ${C_MAX} files.`); return; }
        if (file.size > C_MAX_BYTES)           { errors.push(`"${file.name}" exceeds 10 MB.`); return; }
        const ext = file.name.split('.').pop().toLowerCase();
        if (!C_ALLOWED.includes(ext))          { errors.push(`"${file.name}" type not allowed.`); return; }
        if (cFiles.some(f => f.file.name === file.name && f.file.size === file.size)) {
            errors.push(`"${file.name}" already added.`); return;
        }
        const objectUrl = file.type.startsWith('image/') ? URL.createObjectURL(file) : null;
        cFiles.push({ id: cNextId++, file, objectUrl });
    });

    if (errors.length) cShowErrors(errors);
    cRenderList();
    cRebuildInputs();
}

// ─── Remove ───────────────────────────────────────────────────────────────────
function cRemove(id) {
    const e = cFiles.find(f => f.id === id);
    if (e?.objectUrl) URL.revokeObjectURL(e.objectUrl);
    cFiles = cFiles.filter(f => f.id !== id);
    cRenderList();
    cRebuildInputs();
}

// ─── Render preview chips ─────────────────────────────────────────────────────
const C_TYPE_COLORS = {
    pdf: '#ef4444', doc: '#2563eb', docx: '#2563eb',
    xls: '#16a34a', xlsx: '#16a34a', txt: '#6b7280',
    zip: '#d97706',
};
const C_TYPE_BG = {
    pdf: '#fef2f2', doc: '#eff6ff', docx: '#eff6ff',
    xls: '#f0fdf4', xlsx: '#f0fdf4', txt: '#f9fafb',
    zip: '#fffbeb',
};

function cFormatSize(b) {
    if (b < 1024)    return b + ' B';
    if (b < 1048576) return (b/1024).toFixed(1) + ' KB';
    return (b/1048576).toFixed(1) + ' MB';
}

function cEsc(s) {
    return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function cRenderList() {
    const list  = document.getElementById('c-preview-list');
    const badge = document.getElementById('c-file-count');

    if (cFiles.length === 0) {
        list.classList.add('hidden');
        badge.classList.add('hidden');
        return;
    }

    list.classList.remove('hidden');
    badge.classList.remove('hidden');
    badge.textContent = `${cFiles.length} / ${C_MAX}`;

    list.innerHTML = cFiles.map(({ id, file, objectUrl }) => {
        const ext    = file.name.split('.').pop().toLowerCase();
        const isImg  = !!objectUrl;
        const color  = isImg ? '#8b5cf6' : (C_TYPE_COLORS[ext] ?? '#6b7280');
        const bg     = isImg ? '#f5f3ff'  : (C_TYPE_BG[ext]     ?? '#f9fafb');
        const label  = ext.toUpperCase();
        const nameShort = file.name.length > 30 ? file.name.substring(0,27) + '…' : file.name;

        const thumb = isImg
            ? `<img src="${objectUrl}" class="w-8 h-8 rounded-md object-cover flex-shrink-0 cursor-pointer hover:opacity-90"
                    onclick="cPreviewImage('${objectUrl}','${cEsc(file.name)}')" title="Preview" alt="">`
            : `<div class="w-8 h-8 rounded-md flex items-center justify-center flex-shrink-0 text-[9px] font-bold"
                    style="background:${bg}; color:${color}">${label}</div>`;

        const previewBtn = isImg
            ? `<button type="button" onclick="cPreviewImage('${objectUrl}','${cEsc(file.name)}')"
                       class="text-[11px] text-brand-600 hover:underline">Preview</button>`
            : '';

        return `
        <div class="flex items-center gap-2 px-2.5 py-2 bg-white rounded-lg border border-gray-100 group">
            ${thumb}
            <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-gray-700 truncate" title="${cEsc(file.name)}">${cEsc(nameShort)}</p>
                <div class="flex items-center gap-1.5">
                    <span class="text-[10px] font-bold px-1 py-0.5 rounded"
                          style="background:${bg}; color:${color}">${label}</span>
                    <span class="text-[11px] text-gray-400">${cFormatSize(file.size)}</span>
                    ${previewBtn}
                </div>
            </div>
            <button type="button" onclick="cRemove(${id})" title="Remove"
                    class="flex-shrink-0 p-1 rounded text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors opacity-0 group-hover:opacity-100">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>`;
    }).join('');
}

// ─── Simple image preview modal (no full lightbox needed for comments) ────────
function cPreviewImage(src, name) {
    const modal = document.getElementById('c-img-modal');
    document.getElementById('c-modal-img').src  = src;
    document.getElementById('c-modal-name').textContent = name;
    document.getElementById('c-modal-dl').href   = src;
    document.getElementById('c-modal-dl').download = name;
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function cCloseModal() {
    document.getElementById('c-img-modal').classList.add('hidden');
    document.body.style.overflow = '';
}

// ─── Drag & drop ──────────────────────────────────────────────────────────────
function cHandleSelect(e) { cAddFiles(e.target.files); e.target.value = ''; }
function cDragOver(e)  { e.preventDefault(); e.stopPropagation(); const z=document.getElementById('c-drop-zone'); z.classList.add('border-brand-400','bg-brand-50'); }
function cDragLeave(e) { e.preventDefault(); const z=document.getElementById('c-drop-zone'); z.classList.remove('border-brand-400','bg-brand-50'); }
function cDrop(e)      { e.preventDefault(); e.stopPropagation(); cDragLeave(e); if(e.dataTransfer?.files?.length) cAddFiles(e.dataTransfer.files); }

// ─── Error display ────────────────────────────────────────────────────────────
function cShowErrors(errors) {
    document.querySelectorAll('.c-upload-error').forEach(el => el.remove());
    const wrapper = document.getElementById('c-drop-zone').parentElement;
    const div = document.createElement('div');
    div.className = 'c-upload-error mt-1 space-y-0.5';
    div.innerHTML = errors.map(e => `<p class="text-xs text-red-500">${cEsc(e)}</p>`).join('');
    wrapper.appendChild(div);
    setTimeout(() => div.remove(), 4000);
}

// ─── Submit guard ─────────────────────────────────────────────────────────────
const commentForm = document.getElementById('comment-form');
if (commentForm) {
    commentForm.addEventListener('submit', function() {
        const btn = document.getElementById('comment-submit-btn');
        btn.disabled = true;
        btn.innerHTML = `<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg> Posting…`;
    });
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') cCloseModal(); });
</script>

{{-- ── Simple image preview modal for comment attachments ──────────────── --}}
<div id="c-img-modal"
     class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-6"
     onclick="cCloseModal()">
    <div class="relative max-w-3xl w-full" onclick="event.stopPropagation()">
        <img id="c-modal-img" src="" alt="" class="max-w-full max-h-[80vh] rounded-xl shadow-2xl object-contain mx-auto block">
        <div class="absolute top-3 right-3 flex items-center gap-2">
            <a id="c-modal-dl" href="" download
               class="p-2 rounded-lg bg-white/20 hover:bg-white/30 text-white transition-colors"
               onclick="event.stopPropagation()">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
            </a>
            <button onclick="cCloseModal()" class="p-2 rounded-lg bg-white/20 hover:bg-white/30 text-white">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <p id="c-modal-name" class="text-center text-sm text-white/60 mt-2"></p>
    </div>
</div>
@endpush