@extends('layouts.app')

@section('title', 'New Ticket')
@section('heading', 'New Ticket')
@section('subheading', 'Describe your issue and we\'ll get back to you')

@section('content')
<div class="max-w-2xl">
    <form method="POST"
          action="{{ route('tickets.store') }}"
          enctype="multipart/form-data"
          id="ticket-form"
          class="space-y-5">
        @csrf

        {{-- ── Subject ─────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5 space-y-5">

            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Subject <span class="text-red-500">*</span>
                </label>
                <input type="text" id="subject" name="subject"
                       value="{{ old('subject') }}"
                       placeholder="Brief summary of the issue…"
                       autofocus
                       class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200
                              @error('subject') border-red-400 bg-red-50 @enderror">
                @error('subject')
                <p class="mt-1 text-xs text-red-500 flex items-center gap-1">
                    <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ $message }}
                </p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Description <span class="text-red-500">*</span>
                </label>
                <textarea id="description" name="description" rows="6"
                          placeholder="Provide as much detail as possible — steps to reproduce, error messages, what you expected vs what happened…"
                          class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200 resize-none
                                 @error('description') border-red-400 bg-red-50 @enderror">{{ old('description') }}</textarea>
                @error('description')
                <p class="mt-1 text-xs text-red-500 flex items-center gap-1">
                    <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ $message }}
                </p>
                @enderror
            </div>
        </div>

        {{-- Classification --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Classification</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Case Type <span class="text-red-500">*</span></label>
                    <select name="case_type"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 bg-white @error('case_type') border-red-400 @enderror">
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

                @if(auth()->user()->isAgent())
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Priority <span class="text-red-500">*</span></label>
                        <select name="priority" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 bg-white @error('priority') border-red-400 @enderror">
                            @foreach($priorities as $p)
                            <option value="{{ $p->value }}" {{ old('priority', 'medium') === $p->value ? 'selected' : '' }}>
                                {{ $p->label() }}
                            </option>
                            @endforeach
                        </select>
                        @error('priority')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Priority <span class="text-red-500">*</span></label>
                        <select name="priority" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 bg-white @error('priority') border-red-400 @enderror">
                            @foreach($prioritiesRequester as $p)
                            <option value="{{ $p->value }}" {{ old('priority', 'medium') === $p->value ? 'selected' : '' }}>
                                {{ $p->label() }}
                            </option>
                            @endforeach
                        </select>
                        @error('priority')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Agent <span class="text-red-500">*</span></label>
                    <select name="assignee_id"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 bg-white @error('assignee_id') border-red-400 @enderror">
                        <option value="">Select agent…</option>
                        @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" {{ old('assignee_id') === $agent->id ? 'selected' : '' }}>
                            {{ $agent->name }}
                        </option>
                        {{-- <option value="{{ $agent->id }}" {{ $ticket->assignee_id == $agent->id ? 'selected' : '' }}>
                            {{ $agent->name }} ({{ $agent->email }})
                        </option> --}}
                        @endforeach
                    </select>
                    @error('assignee_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- ATTACHMENT UPLOAD + PREVIEW PANEL                                 --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Attachments</h3>
                    <p class="text-[11px] text-gray-400 mt-0.5">Up to 5 files · 10 MB each · jpg, png, gif, pdf, doc, docx, xls, xlsx, txt, zip</p>
                </div>
                <span id="file-count-badge"
                      class="hidden px-2 py-0.5 bg-brand-100 text-brand-700 text-[11px] font-semibold rounded-full">
                    0 / 5
                </span>
            </div>

            {{-- Drop zone --}}
            <div id="drop-zone"
                 class="relative flex flex-col items-center justify-center w-full min-h-[96px] border-2 border-dashed border-gray-200 rounded-xl cursor-pointer
                        hover:border-brand-400 hover:bg-brand-50 transition-all duration-200 group"
                 onclick="document.getElementById('file-input').click()"
                 ondragover="handleDragOver(event)"
                 ondragleave="handleDragLeave(event)"
                 ondrop="handleDrop(event)">

                <div id="drop-zone-prompt" class="flex flex-col items-center py-4 pointer-events-none">
                    <div class="w-10 h-10 rounded-full bg-gray-50 group-hover:bg-brand-100 flex items-center justify-center mb-2 transition-colors">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-brand-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                        </svg>
                    </div>
                    <p class="text-sm text-gray-500 group-hover:text-brand-600 transition-colors font-medium">
                        Click to upload or drag &amp; drop
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">Files will preview below before submitting</p>
                </div>

                {{-- Hidden real file input --}}
                <input type="file"
                       id="file-input"
                       name="attachments[]"
                       multiple
                       accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip"
                       class="hidden"
                       onchange="handleFileSelect(event)">
            </div>

            {{-- ── Preview grid ──────────────────────────────────────────────── --}}
            <div id="preview-grid"
                 class="hidden mt-4 grid grid-cols-1 gap-2">
                {{-- Cards injected by JS --}}
            </div>

            {{-- Validation errors --}}
            @error('attachments')
            <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
            @enderror
            @error('attachments.*')
            <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- ── Submit ───────────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('tickets.index') }}"
               class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit" id="submit-btn"
                    class="inline-flex items-center gap-2 px-5 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700 transition-colors
                           disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                </svg>
                Submit Ticket
            </button>
        </div>
    </form>
</div>

{{-- ════════════════════════════════════════════════════════════════════════ --}}
{{-- UNIVERSAL FILE PREVIEW MODAL                                           --}}
{{-- Handles: images, PDF, TXT, DOC/DOCX/XLS/XLSX, ZIP                     --}}
{{-- ════════════════════════════════════════════════════════════════════════ --}}
<div id="lightbox"
     class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 md:p-8"
     onclick="closeLightbox()">

    {{-- Modal shell — stops click-through --}}
    <div class="relative w-full max-w-4xl flex flex-col bg-white rounded-2xl shadow-2xl overflow-hidden"
         style="max-height: 90vh;"
         onclick="event.stopPropagation()">

        {{-- ── Header bar ────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 bg-gray-50 flex-shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <div id="lb-type-badge"
                     class="flex-shrink-0 w-7 h-7 rounded-md flex items-center justify-center text-white text-[10px] font-bold">
                </div>
                <div class="min-w-0">
                    <p id="lb-filename" class="text-sm font-semibold text-gray-900 truncate"></p>
                    <p id="lb-filesize" class="text-[11px] text-gray-400"></p>
                </div>
            </div>
            <div class="flex items-center gap-1.5 flex-shrink-0 ml-3">
                {{-- Download --}}
                <a id="lb-download" href="" download
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:border-gray-300 hover:bg-gray-50 transition-colors"
                   onclick="event.stopPropagation()">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    Download
                </a>
                {{-- Close --}}
                <button onclick="closeLightbox()"
                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- ── Preview body (swapped per file type) ──────────────────────── --}}
        <div id="lb-body" class="flex-1 overflow-auto min-h-0 bg-gray-100">

            {{-- IMAGE viewer --}}
            <div id="lb-view-image" class="hidden h-full flex items-center justify-center p-4">
                <img id="lb-img" src="" alt=""
                     class="max-w-full max-h-full object-contain rounded-lg shadow-lg">
            </div>

            {{-- PDF viewer (embedded via <iframe> with object-URL) --}}
            <div id="lb-view-pdf" class="hidden h-full" style="min-height: 60vh;">
                <iframe id="lb-pdf-frame"
                        src=""
                        class="w-full h-full border-0"
                        style="min-height: 60vh;"
                        title="PDF preview">
                </iframe>
            </div>

            {{-- TXT viewer (raw text rendered in <pre>) --}}
            <div id="lb-view-txt" class="hidden h-full overflow-auto bg-white">
                <pre id="lb-txt-content"
                     class="p-5 text-xs text-gray-800 font-mono leading-relaxed whitespace-pre-wrap break-words"></pre>
            </div>

            {{-- DOC / DOCX / XLS / XLSX viewer --}}
            {{-- Uses browser's built-in file URL rendering; works for local files. --}}
            {{-- For .doc/.xls the browser may download instead of preview — that's OK. --}}
            <div id="lb-view-office" class="hidden h-full" style="min-height: 60vh;">
                <div id="lb-office-inner" class="h-full flex flex-col items-center justify-center gap-4 p-8 text-center">
                    {{-- Filled dynamically --}}
                </div>
            </div>

            {{-- ZIP / archive viewer (lists file names from the zip) --}}
            <div id="lb-view-zip" class="hidden h-full overflow-auto bg-white">
                <div class="px-5 py-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Archive Contents</p>
                    <div id="lb-zip-list" class="space-y-1 text-sm font-mono text-gray-700"></div>
                </div>
            </div>

            {{-- Generic / unsupported --}}
            <div id="lb-view-generic" class="hidden h-full flex items-center justify-center p-8 text-center">
                <div>
                    <div id="lb-generic-icon" class="w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-4 text-4xl"></div>
                    <p class="text-sm font-semibold text-gray-700">Preview not available</p>
                    <p class="text-xs text-gray-400 mt-1">Click Download to open this file on your device</p>
                </div>
            </div>
        </div>

        {{-- ── Footer: navigation for multiple attachments ───────────────── --}}
        <div id="lb-nav" class="hidden flex items-center justify-between px-5 py-2.5 border-t border-gray-100 bg-gray-50 flex-shrink-0">
            <button id="lb-prev" onclick="navigateLightbox(-1)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-white disabled:opacity-40 disabled:cursor-not-allowed">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Previous
            </button>
            <span id="lb-nav-label" class="text-xs text-gray-400"></span>
            <button id="lb-next" onclick="navigateLightbox(1)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-white disabled:opacity-40 disabled:cursor-not-allowed">
                Next
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ─── State ────────────────────────────────────────────────────────────────────
const MAX_FILES    = 5;
const MAX_SIZE_MB  = 10;
const MAX_SIZE_B   = MAX_SIZE_MB * 1024 * 1024;
const ALLOWED_MIME = [
    'image/jpeg','image/jpg','image/png','image/gif',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'text/plain',
    'application/zip','application/x-zip-compressed',
];

// Stores { file, objectUrl, id } for each selected file
let selectedFiles = [];
let nextId        = 0;

// ─── File type metadata ───────────────────────────────────────────────────────
function getFileMeta(file) {
    const ext  = file.name.split('.').pop().toLowerCase();
    const mime = file.type;
    const isImage = mime.startsWith('image/');

    const iconMap = {
        pdf:  { color: '#ef4444', bg: '#fef2f2', label: 'PDF',  icon: pdfIcon() },
        doc:  { color: '#2563eb', bg: '#eff6ff', label: 'DOC',  icon: wordIcon() },
        docx: { color: '#2563eb', bg: '#eff6ff', label: 'DOCX', icon: wordIcon() },
        xls:  { color: '#16a34a', bg: '#f0fdf4', label: 'XLS',  icon: excelIcon() },
        xlsx: { color: '#16a34a', bg: '#f0fdf4', label: 'XLSX', icon: excelIcon() },
        txt:  { color: '#6b7280', bg: '#f9fafb', label: 'TXT',  icon: textIcon() },
        zip:  { color: '#d97706', bg: '#fffbeb', label: 'ZIP',  icon: zipIcon() },
    };

    if (isImage) return { isImage: true, color: '#8b5cf6', bg: '#f5f3ff', label: ext.toUpperCase() };
    return iconMap[ext] ?? { color: '#6b7280', bg: '#f9fafb', label: ext.toUpperCase(), icon: fileIcon() };
}

// ─── SVG icon helpers ─────────────────────────────────────────────────────────
function pdfIcon()   { return `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 7V3.5L18.5 9H13z"/><text x="5" y="19" font-size="5" font-weight="bold" fill="white">PDF</text></svg>`; }
function wordIcon()  { return `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 7V3.5L18.5 9H13z"/></svg>`; }
function excelIcon() { return `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 7V3.5L18.5 9H13z"/></svg>`; }
function textIcon()  { return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`; }
function zipIcon()   { return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>`; }
function fileIcon()  { return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>`; }

// ─── Format helpers ───────────────────────────────────────────────────────────
function formatSize(bytes) {
    if (bytes < 1024)        return bytes + ' B';
    if (bytes < 1048576)     return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
}

function escHtml(str) {
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ─── Core: add files ──────────────────────────────────────────────────────────
function addFiles(fileList) {
    const errors = [];
    let added    = 0;

    Array.from(fileList).forEach(file => {
        if (selectedFiles.length >= MAX_FILES) {
            errors.push(`Maximum ${MAX_FILES} files allowed.`);
            return;
        }
        if (file.size > MAX_SIZE_B) {
            errors.push(`"${file.name}" exceeds ${MAX_SIZE_MB} MB limit.`);
            return;
        }
        if (!ALLOWED_MIME.includes(file.type)) {
            errors.push(`"${file.name}" — file type not allowed.`);
            return;
        }
        // Deduplicate by name+size
        if (selectedFiles.some(f => f.file.name === file.name && f.file.size === file.size)) {
            errors.push(`"${file.name}" already added.`);
            return;
        }

        const objectUrl = file.type.startsWith('image/') ? URL.createObjectURL(file) : null;
        selectedFiles.push({ file, objectUrl, id: nextId++ });
        added++;
    });

    if (errors.length) showErrors(errors);
    if (added)         renderPreviews();
    syncFileInput();
}

// ─── Render preview cards ─────────────────────────────────────────────────────
function renderPreviews() {
    const grid  = document.getElementById('preview-grid');
    const badge = document.getElementById('file-count-badge');

    if (selectedFiles.length === 0) {
        grid.classList.add('hidden');
        badge.classList.add('hidden');
        return;
    }

    grid.classList.remove('hidden');
    badge.classList.remove('hidden');
    badge.textContent = `${selectedFiles.length} / ${MAX_FILES}`;
    badge.className = selectedFiles.length >= MAX_FILES
        ? 'px-2 py-0.5 bg-amber-100 text-amber-700 text-[11px] font-semibold rounded-full'
        : 'px-2 py-0.5 bg-brand-100 text-brand-700 text-[11px] font-semibold rounded-full';

    grid.innerHTML = selectedFiles.map(entry => buildCard(entry)).join('');
}

function buildCard({ file, objectUrl, id }) {
    const meta      = getFileMeta(file);
    const sizeStr   = formatSize(file.size);
    const nameShort = file.name.length > 36 ? file.name.substring(0, 33) + '…' : file.name;
    const isImage   = !!objectUrl;

    // Thumbnail area
    const thumbnailHtml = isImage
        ? `<button type="button"
                   onclick="openLightbox(${id})"
                   class="flex-shrink-0 w-14 h-14 rounded-lg overflow-hidden border border-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-400 group/thumb"
                   title="Click to preview">
               <img src="${objectUrl}" alt="${escHtml(file.name)}"
                    class="w-full h-full object-cover group-hover/thumb:scale-105 transition-transform duration-200">
           </button>`
        : `<button type="button"
                   onclick="openLightbox(${id})"
                   class="flex-shrink-0 w-14 h-14 rounded-lg flex items-center justify-center border border-gray-100 hover:border-gray-300 transition-colors"
                   style="background:${meta.bg}; color:${meta.color}"
                   title="Click to preview">
               <div class="w-7 h-7">${meta.icon ?? fileIcon()}</div>
           </button>`;

    // Preview/open button — available for ALL file types now
    const actionBtn = `<button type="button"
               onclick="openLightbox(${id})"
               class="text-[11px] text-brand-600 hover:text-brand-800 font-medium hover:underline flex items-center gap-0.5">
               <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
               Preview
           </button>`;

    return `
    <div id="card-${id}"
         class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 bg-gray-50 hover:border-gray-200 hover:bg-white transition-all group/card">

        ${thumbnailHtml}

        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-800 truncate" title="${escHtml(file.name)}">
                ${escHtml(nameShort)}
            </p>
            <div class="flex items-center gap-2 mt-0.5">
                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase"
                      style="background:${meta.bg}; color:${meta.color}">
                    ${meta.label}
                </span>
                <span class="text-[11px] text-gray-400">${sizeStr}</span>
                ${actionBtn}
            </div>
        </div>

        {{-- Remove button --}}
        <button type="button"
                onclick="removeFile(${id})"
                title="Remove file"
                class="flex-shrink-0 p-1.5 rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors opacity-0 group-hover/card:opacity-100">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </button>
    </div>`;
}

// ─── Remove a file ────────────────────────────────────────────────────────────
function removeFile(id) {
    const entry = selectedFiles.find(f => f.id === id);
    if (entry?.objectUrl) URL.revokeObjectURL(entry.objectUrl);

    selectedFiles = selectedFiles.filter(f => f.id !== id);
    renderPreviews();
    syncFileInput();
}

// ─── Sync real <input type="file"> with our selectedFiles list ───────────────
// The browser won't let us set FileList directly, so we rebuild the input
// using a DataTransfer object — supported in all modern browsers.
function syncFileInput() {
    const input = document.getElementById('file-input');
    try {
        const dt = new DataTransfer();
        selectedFiles.forEach(({ file }) => dt.items.add(file));
        input.files = dt.files;
    } catch (e) {
        // DataTransfer not supported (rare) — submit will use whatever is in input
        console.warn('DataTransfer not supported, file sync skipped', e);
    }
}

// ─── Error banner ─────────────────────────────────────────────────────────────
function showErrors(errors) {
    // Remove existing
    document.querySelectorAll('.upload-error').forEach(el => el.remove());

    const wrapper = document.getElementById('drop-zone').parentElement;
    const div = document.createElement('div');
    div.className = 'upload-error mt-2 space-y-1';
    div.innerHTML = errors.map(e =>
        `<p class="text-xs text-red-500 flex items-center gap-1">
            <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            ${escHtml(e)}
        </p>`
    ).join('');
    wrapper.appendChild(div);

    setTimeout(() => div.remove(), 5000);
}

// ─── Event handlers ───────────────────────────────────────────────────────────
function handleFileSelect(event) {
    addFiles(event.target.files);
    // Reset the input value so selecting the same file again triggers change
    event.target.value = '';
}

function handleDragOver(event) {
    event.preventDefault();
    event.stopPropagation();
    const zone = document.getElementById('drop-zone');
    zone.classList.add('border-brand-400', 'bg-brand-50');
    zone.classList.remove('border-gray-200');
}

function handleDragLeave(event) {
    event.preventDefault();
    const zone = document.getElementById('drop-zone');
    zone.classList.remove('border-brand-400', 'bg-brand-50');
    zone.classList.add('border-gray-200');
}

function handleDrop(event) {
    event.preventDefault();
    event.stopPropagation();
    handleDragLeave(event);
    if (event.dataTransfer?.files?.length) {
        addFiles(event.dataTransfer.files);
    }
}

// ─── Universal preview modal ──────────────────────────────────────────────────

// Track which file is currently open (index into selectedFiles)
let currentLightboxId = null;

// File-type → viewer panel ID + badge colour
const VIEWER_MAP = {
    image: { panel: 'lb-view-image',   bg: '#8b5cf6', label: 'IMG'  },
    pdf:   { panel: 'lb-view-pdf',     bg: '#ef4444', label: 'PDF'  },
    txt:   { panel: 'lb-view-txt',     bg: '#6b7280', label: 'TXT'  },
    doc:   { panel: 'lb-view-office',  bg: '#2563eb', label: 'DOC'  },
    docx:  { panel: 'lb-view-office',  bg: '#2563eb', label: 'DOCX' },
    xls:   { panel: 'lb-view-office',  bg: '#16a34a', label: 'XLS'  },
    xlsx:  { panel: 'lb-view-office',  bg: '#16a34a', label: 'XLSX' },
    zip:   { panel: 'lb-view-zip',     bg: '#d97706', label: 'ZIP'  },
};

function openLightbox(fileId) {
    const entry = selectedFiles.find(f => f.id === fileId);
    if (!entry) return;

    currentLightboxId = fileId;
    loadIntoLightbox(entry);

    document.getElementById('lightbox').classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Show nav arrows if more than 1 file
    const nav = document.getElementById('lb-nav');
    if (selectedFiles.length > 1) {
        nav.classList.remove('hidden');
        updateNavState();
    } else {
        nav.classList.add('hidden');
    }
}

function resetLightboxViews() {
    // Hide semua panel
    [
        'lb-view-image',
        'lb-view-pdf',
        'lb-view-txt',
        'lb-view-office',
        'lb-view-zip',
        'lb-view-generic'
    ].forEach(id => {
        const el = document.getElementById(id);

        el.classList.add('hidden');
        el.style.display = '';
    });

    // Reset image
    document.getElementById('lb-img').src = '';

    // Reset PDF
    document.getElementById('lb-pdf-frame').src = '';

    // Reset TXT
    document.getElementById('lb-txt-content').textContent = '';

    // Reset ZIP
    document.getElementById('lb-zip-list').innerHTML = '';

    // Reset Office
    document.getElementById('lb-office-inner').innerHTML = '';
}

function loadIntoLightbox(entry) {
    const { file, objectUrl } = entry;
    const ext = file.name.split('.').pop().toLowerCase();
    const isImage = file.type.startsWith('image/');
    const typeKey = isImage ? 'image' : (ext in VIEWER_MAP ? ext : null);
    const vm = typeKey ? VIEWER_MAP[typeKey] : null;

    // ── Header ────────────────────────────────────────────────────────────────
    document.getElementById('lb-filename').textContent = file.name;
    document.getElementById('lb-filesize').textContent = formatSize(file.size);

    const badge = document.getElementById('lb-type-badge');
    badge.textContent = vm?.label ?? ext.toUpperCase();
    badge.style.background = vm?.bg ?? '#6b7280';

    // Download link — always works via objectUrl for images/pdf/txt,
    // for office/zip we generate a fresh object URL just for downloading
    const dlUrl = objectUrl ?? URL.createObjectURL(file);
    const dl = document.getElementById('lb-download');
    dl.href     = dlUrl;
    dl.download = file.name;

    // ── Hide all panels ───────────────────────────────────────────────────────
    resetLightboxViews();

    // ── Body height ───────────────────────────────────────────────────────────
    const body = document.getElementById('lb-body');
    body.style.minHeight = '60vh';

    // ── Load the right panel ──────────────────────────────────────────────────
    if (isImage) {
        // ── IMAGE ─────────────────────────────────────────────────────────────
        const panel = document.getElementById('lb-view-image');
        panel.classList.remove('hidden');
        panel.style.minHeight = '60vh';
        panel.style.display   = 'flex';
        document.getElementById('lb-img').src = objectUrl;

    } else if (ext === 'pdf') {
        // ── PDF ───────────────────────────────────────────────────────────────
        const pdfUrl = URL.createObjectURL(file);
        const panel  = document.getElementById('lb-view-pdf');
        panel.classList.remove('hidden');
        panel.style.minHeight = '70vh';
        // Embed with #toolbar=1 for Chrome/Edge PDF viewer toolbar
        document.getElementById('lb-pdf-frame').src = pdfUrl + '#toolbar=1&view=FitH';

    } else if (ext === 'txt') {
        // ── TXT ───────────────────────────────────────────────────────────────
        const reader = new FileReader();
        reader.onload = e => {
            const pre = document.getElementById('lb-txt-content');
            pre.textContent = e.target.result;
        };
        reader.readAsText(file);
        document.getElementById('lb-view-txt').classList.remove('hidden');

    } else if (['doc','docx','xls','xlsx'].includes(ext)) {
        // ── OFFICE docs ───────────────────────────────────────────────────────
        // Strategy: generate a local blob URL and try an <iframe>.
        // Most browsers cannot render .doc/.xls natively, so we show a
        // well-designed fallback with a "Download & Open" CTA.
        const officeUrl = URL.createObjectURL(file);
        const panel     = document.getElementById('lb-view-office');
        const inner     = document.getElementById('lb-office-inner');
        panel.classList.remove('hidden');
        panel.style.minHeight = '60vh';
        panel.style.display   = 'flex';

        const iconHtml  = ext.includes('xls') ? excelIcon() : wordIcon();
        const iconColor = ext.includes('xls') ? '#16a34a'   : '#2563eb';
        const iconBg    = ext.includes('xls') ? '#f0fdf4'   : '#eff6ff';
        const appName   = ext.includes('xls') ? 'Microsoft Excel' : 'Microsoft Word';

        inner.innerHTML = `
            <div class="w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-4"
                 style="background:${iconBg}; color:${iconColor}">
                <div class="w-10 h-10">${iconHtml}</div>
            </div>
            <p class="text-base font-semibold text-gray-800">${escHtml(file.name)}</p>
            <p class="text-sm text-gray-400 mt-1">${formatSize(file.size)}</p>
            <p class="text-xs text-gray-400 mt-3 max-w-xs">
                Browser preview is not supported for ${escHtml(ext.toUpperCase())} files.
                Download the file to open it in ${appName}.
            </p>
            <a href="${officeUrl}" download="${escHtml(file.name)}"
               class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white rounded-xl transition-colors"
               style="background:${iconColor}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                Download &amp; Open
            </a>`;

    } else if (ext === 'zip') {
        // ── ZIP ───────────────────────────────────────────────────────────────
        // Use the JSZip CDN (loaded lazily) to list the archive contents.
        const panel = document.getElementById('lb-view-zip');
        panel.classList.remove('hidden');
        const listEl = document.getElementById('lb-zip-list');
        const openedFileId = currentLightboxId;
        listEl.innerHTML = `<p class="text-xs text-gray-400">Reading archive…</p>`;
        loadJSZip().then(JSZip => {

            JSZip.loadAsync(file).then(zip => {
                if (openedFileId !== currentLightboxId) {
                    return;
                }
                const entries = Object.values(zip.files);

                const sorted = entries.sort((a, b) => {
                    if (a.dir !== b.dir) return a.dir ? -1 : 1;
                    return a.name.localeCompare(b.name);
                });
                listEl.innerHTML = sorted.map(entry => {
                    const icon = entry.dir
                        ? `<svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>`
                        : `<svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`;
                    const sizeLabel = (!entry.dir && entry._data?.uncompressedSize)
                        ? `<span class="text-[11px] text-gray-400 ml-auto">${formatSize(entry._data.uncompressedSize)}</span>`
                        : '';
                    return `<div class="flex items-center gap-2 py-1 px-2 rounded hover:bg-gray-50">
                        ${icon}
                        <span class="text-xs ${entry.dir ? 'font-semibold text-gray-700' : 'text-gray-600'} truncate">${escHtml(entry.name)}</span>
                        ${sizeLabel}
                    </div>`;
                }).join('');
            }).catch(() => {
                listEl.innerHTML = `<p class="text-xs text-red-500">Could not read archive contents.</p>`;
            });
        });

    } else {
        // ── Generic fallback ──────────────────────────────────────────────────
        const panel = document.getElementById('lb-view-generic');
        panel.classList.remove('hidden');
        panel.style.minHeight = '60vh';
        panel.style.display   = 'flex';
        const meta = getFileMeta(file);
        const iconEl = document.getElementById('lb-generic-icon');
        iconEl.innerHTML = meta.icon ?? fileIcon();
        iconEl.style.background = meta.bg;
        iconEl.style.color      = meta.color;
    }
}

function closeLightbox() {
    document.getElementById('lightbox').classList.add('hidden');
    document.body.style.overflow = '';
    // Clear iframe src to stop PDF rendering when closed
    document.getElementById('lb-pdf-frame').src = '';
    currentLightboxId = null;
}

// Navigate between files while lightbox is open
function navigateLightbox(direction) {
    const idx = selectedFiles.findIndex(f => f.id === currentLightboxId);
    const next = idx + direction;
    if (next < 0 || next >= selectedFiles.length) return;
    currentLightboxId = selectedFiles[next].id;
    loadIntoLightbox(selectedFiles[next]);
    updateNavState();
}

function updateNavState() {
    const idx  = selectedFiles.findIndex(f => f.id === currentLightboxId);
    const prev = document.getElementById('lb-prev');
    const next = document.getElementById('lb-next');
    const lbl  = document.getElementById('lb-nav-label');

    prev.disabled = idx === 0;
    next.disabled = idx === selectedFiles.length - 1;
    lbl.textContent = `${idx + 1} of ${selectedFiles.length}`;
}

// ─── Lazy-load JSZip for ZIP preview ─────────────────────────────────────────
let _jszip = null;
function loadJSZip() {
    if (_jszip) return _jszip;
    _jszip = new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js';
        script.onload  = () => resolve(window.JSZip);
        script.onerror = reject;
        document.head.appendChild(script);
    });
    return _jszip;
}

// Close lightbox on Escape key
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeLightbox();
});

// ─── Form submit guard ────────────────────────────────────────────────────────
document.getElementById('ticket-form').addEventListener('submit', function (e) {
    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.innerHTML = `
        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Submitting…`;
});
</script>
@endpush