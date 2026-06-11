@extends('layouts.app')

@section('title', 'New Ticket')
@section('heading', 'New Ticket')
@section('subheading', "Describe your issue and we'll get back to you")

@section('content')
<div class="max-w-2xl">
    <form method="POST"
          action="{{ route('tickets.store') }}"
          enctype="multipart/form-data"
          id="ticket-form"
          class="space-y-5">
        @csrf

        {{-- ── Subject + Description ────────────────────────────────────────── --}}
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
                          placeholder="Steps to reproduce, error messages, what you expected vs what happened…"
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
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- ATTACHMENT PANEL                                                   --}}
        {{-- Real file inputs are injected into #file-inputs-container on      --}}
        {{-- selection. Each file gets its own <input type="file"> element.    --}}
        {{-- This avoids the DataTransfer.items.add() bug on localhost (HTTP). --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5">

            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Attachments</h3>
                    <p class="text-[11px] text-gray-400 mt-0.5">Up to 5 files · 10 MB each · jpg, png, gif, pdf, doc, docx, xls, xlsx, txt, zip</p>
                </div>
                <span id="file-count-badge" class="hidden px-2 py-0.5 bg-brand-100 text-brand-700 text-[11px] font-semibold rounded-full">
                    0 / 5
                </span>
            </div>

            {{-- ── Hidden container: one real <input type="file"> per selected file ── --}}
            {{-- These are what actually get submitted with the form.                   --}}
            <div id="file-inputs-container" class="hidden" aria-hidden="true"></div>

            {{-- ── Visible trigger input (never submitted) ────────────────────────── --}}
            <input type="file"
                   id="file-trigger"
                   multiple
                   accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip"
                   class="hidden"
                   onchange="handleFileSelect(event)">

            {{-- ── Drop zone ─────────────────────────────────────────────────────── --}}
            <div id="drop-zone"
                 class="relative flex flex-col items-center justify-center w-full min-h-[96px] border-2 border-dashed border-gray-200 rounded-xl cursor-pointer
                        hover:border-brand-400 hover:bg-brand-50 transition-all duration-200 group"
                 onclick="document.getElementById('file-trigger').click()"
                 ondragover="handleDragOver(event)"
                 ondragleave="handleDragLeave(event)"
                 ondrop="handleDrop(event)">

                <div class="flex flex-col items-center py-4 pointer-events-none">
                    <div class="w-10 h-10 rounded-full bg-gray-50 group-hover:bg-brand-100 flex items-center justify-center mb-2 transition-colors">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-brand-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                        </svg>
                    </div>
                    <p class="text-sm text-gray-500 group-hover:text-brand-600 font-medium transition-colors">
                        Click to upload or drag &amp; drop
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">Files preview instantly before submitting</p>
                </div>
            </div>

            {{-- ── Preview grid ──────────────────────────────────────────────────── --}}
            <div id="preview-grid" class="hidden mt-4 space-y-2"></div>

            @error('attachments')   <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
            @error('attachments.*') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- ── Submit ───────────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('tickets.index') }}"
               class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit" id="submit-btn"
                    class="inline-flex items-center gap-2 px-5 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                </svg>
                Submit Ticket
            </button>
        </div>
    </form>
</div>

{{-- ════════════════════════════════════════════════════════════════════════ --}}
{{-- UNIVERSAL FILE PREVIEW MODAL                                            --}}
{{-- ════════════════════════════════════════════════════════════════════════ --}}
<div id="lightbox"
     class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 md:p-8"
     onclick="closeLightbox()">
    <div class="relative w-full max-w-4xl flex flex-col bg-white rounded-2xl shadow-2xl overflow-hidden"
         style="max-height:90vh;" onclick="event.stopPropagation()">

        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 bg-gray-50 flex-shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <div id="lb-type-badge" class="flex-shrink-0 w-7 h-7 rounded-md flex items-center justify-center text-white text-[10px] font-bold"></div>
                <div class="min-w-0">
                    <p id="lb-filename" class="text-sm font-semibold text-gray-900 truncate"></p>
                    <p id="lb-filesize" class="text-[11px] text-gray-400"></p>
                </div>
            </div>
            <div class="flex items-center gap-1.5 flex-shrink-0 ml-3">
                <a id="lb-download" href="" download
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50"
                   onclick="event.stopPropagation()">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Download
                </a>
                <button onclick="closeLightbox()" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <div id="lb-body" class="flex-1 overflow-auto min-h-0 bg-gray-100">
            <div id="lb-view-image"   class="hidden h-full flex items-center justify-center p-4" style="min-height:60vh">
                <img id="lb-img" src="" alt="" class="max-w-full max-h-full object-contain rounded-lg shadow-lg">
            </div>
            <div id="lb-view-pdf"     class="hidden h-full" style="min-height:60vh">
                <iframe id="lb-pdf-frame" src="" class="w-full h-full border-0" style="min-height:60vh" title="PDF preview"></iframe>
            </div>
            <div id="lb-view-txt"     class="hidden h-full overflow-auto bg-white">
                <pre id="lb-txt-content" class="p-5 text-xs text-gray-800 font-mono leading-relaxed whitespace-pre-wrap break-words"></pre>
            </div>
            <div id="lb-view-office"  class="hidden h-full" style="min-height:60vh">
                <div id="lb-office-inner" class="h-full flex flex-col items-center justify-center gap-4 p-8 text-center"></div>
            </div>
            <div id="lb-view-zip"     class="hidden h-full overflow-auto bg-white">
                <div class="px-5 py-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Archive Contents</p>
                    <div id="lb-zip-list" class="space-y-1 text-sm font-mono text-gray-700"></div>
                </div>
            </div>
            <div id="lb-view-generic" class="hidden h-full flex items-center justify-center p-8 text-center" style="min-height:60vh">
                <div>
                    <div id="lb-generic-icon" class="w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-4 text-4xl"></div>
                    <p class="text-sm font-semibold text-gray-700">Preview not available</p>
                    <p class="text-xs text-gray-400 mt-1">Click Download to open on your device</p>
                </div>
            </div>
        </div>

        <div id="lb-nav" class="hidden flex items-center justify-between px-5 py-2.5 border-t border-gray-100 bg-gray-50 flex-shrink-0">
            <button id="lb-prev" onclick="navigateLightbox(-1)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-white disabled:opacity-40">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg> Previous
            </button>
            <span id="lb-nav-label" class="text-xs text-gray-400"></span>
            <button id="lb-next" onclick="navigateLightbox(1)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-white disabled:opacity-40">
                Next <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ─── Constants ────────────────────────────────────────────────────────────────
const MAX_FILES   = 5;
const MAX_SIZE_MB = 10;
const MAX_SIZE_B  = MAX_SIZE_MB * 1024 * 1024;
const ALLOWED_EXT = ['jpg','jpeg','png','gif','pdf','doc','docx','xls','xlsx','txt','zip'];

// ─── State ────────────────────────────────────────────────────────────────────
// Each entry: { id, file, objectUrl }
// objectUrl is only set for images (used for thumbnail + lightbox).
let selectedFiles      = [];
let nextId             = 0;
let currentLightboxId  = null;

// ─── Key fix: inject one real <input type="file"> per selected file ───────────
//
// WHY THIS APPROACH:
//   The old code used DataTransfer.items.add(file) to set input.files
//   on a single <input>. On localhost (HTTP, not HTTPS), Chromium blocks
//   DataTransfer.items.add() silently, leaving FileList {length: 0}.
//
// THE FIX:
//   For each File in selectedFiles, create a hidden <input type="file">
//   named "attachments[]", build a single-item DataTransfer, and assign
//   its FileList to that input. Each input carries exactly ONE file, so
//   even if DataTransfer has issues with multiple items it only needs to
//   handle one at a time. All inputs live in #file-inputs-container and
//   are submitted alongside the form naturally.
//
function rebuildFileInputs() {
    const container = document.getElementById('file-inputs-container');
    container.innerHTML = '';   // clear previous inputs

    selectedFiles.forEach(({ file }) => {
        const input = document.createElement('input');
        input.type     = 'file';
        input.name     = 'attachments[]';
        input.style.display = 'none';

        // Assign the File via a fresh single-item DataTransfer
        try {
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
        } catch (e) {
            // DataTransfer completely unsupported — extremely rare
            console.warn('DataTransfer not supported for file:', file.name, e);
        }

        container.appendChild(input);
    });

    // Show/hide container (it's aria-hidden, just for form submission)
    container.classList.toggle('hidden', selectedFiles.length === 0);
}

// ─── Add files ────────────────────────────────────────────────────────────────
function addFiles(fileList) {
    const errors = [];
    let added = 0;

    Array.from(fileList).forEach(file => {
        if (selectedFiles.length >= MAX_FILES) {
            errors.push(`Maximum ${MAX_FILES} files allowed.`);
            return;
        }
        if (file.size > MAX_SIZE_B) {
            errors.push(`"${file.name}" exceeds ${MAX_SIZE_MB} MB.`);
            return;
        }
        const ext = file.name.split('.').pop().toLowerCase();
        if (!ALLOWED_EXT.includes(ext)) {
            errors.push(`"${file.name}" — file type not allowed.`);
            return;
        }
        if (selectedFiles.some(f => f.file.name === file.name && f.file.size === file.size)) {
            errors.push(`"${file.name}" already added.`);
            return;
        }

        const objectUrl = file.type.startsWith('image/') ? URL.createObjectURL(file) : null;
        selectedFiles.push({ id: nextId++, file, objectUrl });
        added++;
    });

    if (errors.length) showErrors(errors);
    if (added) {
        renderPreviews();
        rebuildFileInputs();   // ← rebuild real inputs every time files change
    }
}

// ─── Remove file ──────────────────────────────────────────────────────────────
function removeFile(id) {
    const entry = selectedFiles.find(f => f.id === id);
    if (entry?.objectUrl) URL.revokeObjectURL(entry.objectUrl);

    selectedFiles = selectedFiles.filter(f => f.id !== id);
    renderPreviews();
    rebuildFileInputs();   // ← sync inputs after removal
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

// ─── File type metadata ───────────────────────────────────────────────────────
const TYPE_META = {
    pdf:  { bg:'#fef2f2', color:'#ef4444', label:'PDF'  },
    doc:  { bg:'#eff6ff', color:'#2563eb', label:'DOC'  },
    docx: { bg:'#eff6ff', color:'#2563eb', label:'DOCX' },
    xls:  { bg:'#f0fdf4', color:'#16a34a', label:'XLS'  },
    xlsx: { bg:'#f0fdf4', color:'#16a34a', label:'XLSX' },
    txt:  { bg:'#f9fafb', color:'#6b7280', label:'TXT'  },
    zip:  { bg:'#fffbeb', color:'#d97706', label:'ZIP'  },
};

function getFileMeta(file) {
    const ext = file.name.split('.').pop().toLowerCase();
    if (file.type.startsWith('image/')) return { bg:'#f5f3ff', color:'#8b5cf6', label: ext.toUpperCase(), isImage: true };
    return { ...(TYPE_META[ext] ?? { bg:'#f9fafb', color:'#6b7280', label: ext.toUpperCase() }), isImage: false };
}

function formatSize(bytes) {
    if (bytes < 1024)    return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
}

function escHtml(s) {
    return String(s ?? '')
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ─── Build a single preview card ──────────────────────────────────────────────
function buildCard({ id, file, objectUrl }) {
    const meta      = getFileMeta(file);
    const nameShort = file.name.length > 38 ? file.name.substring(0, 35) + '…' : file.name;

    const thumbHtml = meta.isImage
        ? `<button type="button" onclick="openLightbox(${id})"
               class="flex-shrink-0 w-14 h-14 rounded-lg overflow-hidden border border-gray-100 group/thumb" title="Preview">
               <img src="${objectUrl}" class="w-full h-full object-cover group-hover/thumb:scale-105 transition-transform duration-200" alt="">
           </button>`
        : `<button type="button" onclick="openLightbox(${id})"
               class="flex-shrink-0 w-14 h-14 rounded-lg flex items-center justify-center border border-gray-100 hover:border-gray-300 transition-colors"
               style="background:${meta.bg}; color:${meta.color}" title="Preview">
               <span class="text-[10px] font-bold">${meta.label}</span>
           </button>`;

    return `
    <div id="card-${id}" class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 bg-gray-50 hover:border-gray-200 hover:bg-white transition-all group/card">
        ${thumbHtml}
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-800 truncate" title="${escHtml(file.name)}">${escHtml(nameShort)}</p>
            <div class="flex items-center gap-2 mt-0.5">
                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase" style="background:${meta.bg}; color:${meta.color}">${meta.label}</span>
                <span class="text-[11px] text-gray-400">${formatSize(file.size)}</span>
                <button type="button" onclick="openLightbox(${id})"
                        class="text-[11px] text-brand-600 hover:text-brand-800 font-medium hover:underline flex items-center gap-0.5">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Preview
                </button>
            </div>
        </div>
        <button type="button" onclick="removeFile(${id})" title="Remove"
                class="flex-shrink-0 p-1.5 rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors opacity-0 group-hover/card:opacity-100">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>
    </div>`;
}

// ─── Error display ────────────────────────────────────────────────────────────
function showErrors(errors) {
    document.querySelectorAll('.upload-error').forEach(el => el.remove());
    const wrapper = document.getElementById('drop-zone').parentElement;
    const div = document.createElement('div');
    div.className = 'upload-error mt-2 space-y-1';
    div.innerHTML = errors.map(e =>
        `<p class="text-xs text-red-500 flex items-center gap-1">
            <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            ${escHtml(e)}</p>`
    ).join('');
    wrapper.appendChild(div);
    setTimeout(() => div.remove(), 5000);
}

// ─── Event handlers ───────────────────────────────────────────────────────────
function handleFileSelect(e) {
    addFiles(e.target.files);
    e.target.value = '';   // reset so same file can be re-selected
}

function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    const z = document.getElementById('drop-zone');
    z.classList.add('border-brand-400', 'bg-brand-50');
    z.classList.remove('border-gray-200');
}

function handleDragLeave(e) {
    e.preventDefault();
    const z = document.getElementById('drop-zone');
    z.classList.remove('border-brand-400', 'bg-brand-50');
    z.classList.add('border-gray-200');
}

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    handleDragLeave(e);
    if (e.dataTransfer?.files?.length) addFiles(e.dataTransfer.files);
}

// ─── Lightbox ─────────────────────────────────────────────────────────────────
const VIEWER_MAP = {
    image: { panel:'lb-view-image',  bg:'#8b5cf6', label:'IMG'  },
    pdf:   { panel:'lb-view-pdf',    bg:'#ef4444', label:'PDF'  },
    txt:   { panel:'lb-view-txt',    bg:'#6b7280', label:'TXT'  },
    doc:   { panel:'lb-view-office', bg:'#2563eb', label:'DOC'  },
    docx:  { panel:'lb-view-office', bg:'#2563eb', label:'DOCX' },
    xls:   { panel:'lb-view-office', bg:'#16a34a', label:'XLS'  },
    xlsx:  { panel:'lb-view-office', bg:'#16a34a', label:'XLSX' },
    zip:   { panel:'lb-view-zip',    bg:'#d97706', label:'ZIP'  },
};

function openLightbox(fileId) {
    const entry = selectedFiles.find(f => f.id === fileId);
    if (!entry) return;
    currentLightboxId = fileId;
    loadIntoLightbox(entry);
    document.getElementById('lightbox').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    const nav = document.getElementById('lb-nav');
    if (selectedFiles.length > 1) { nav.classList.remove('hidden'); updateNavState(); }
    else nav.classList.add('hidden');
}

function loadIntoLightbox({ file, objectUrl }) {
    const ext     = file.name.split('.').pop().toLowerCase();
    const isImage = file.type.startsWith('image/');
    const typeKey = isImage ? 'image' : (ext in VIEWER_MAP ? ext : null);
    const vm      = typeKey ? VIEWER_MAP[typeKey] : null;

    document.getElementById('lb-filename').textContent = file.name;
    document.getElementById('lb-filesize').textContent = formatSize(file.size);
    const badge = document.getElementById('lb-type-badge');
    badge.textContent  = vm?.label ?? ext.toUpperCase();
    badge.style.background = vm?.bg ?? '#6b7280';

    const dlUrl = objectUrl ?? URL.createObjectURL(file);
    const dl = document.getElementById('lb-download');
    dl.href = dlUrl; dl.download = file.name;

    ['lb-view-image','lb-view-pdf','lb-view-txt','lb-view-office','lb-view-zip','lb-view-generic']
        .forEach(id => document.getElementById(id).classList.add('hidden'));

    if (isImage) {
        const p = document.getElementById('lb-view-image');
        p.classList.remove('hidden'); p.style.display = 'flex';
        document.getElementById('lb-img').src = objectUrl;
    } else if (ext === 'pdf') {
        const pdfUrl = URL.createObjectURL(file);
        const p = document.getElementById('lb-view-pdf');
        p.classList.remove('hidden'); p.style.minHeight = '70vh';
        document.getElementById('lb-pdf-frame').src = pdfUrl + '#toolbar=1&view=FitH';
    } else if (ext === 'txt') {
        const reader = new FileReader();
        reader.onload = e => { document.getElementById('lb-txt-content').textContent = e.target.result; };
        reader.readAsText(file);
        document.getElementById('lb-view-txt').classList.remove('hidden');
    } else if (['doc','docx','xls','xlsx'].includes(ext)) {
        const officeUrl = URL.createObjectURL(file);
        const p = document.getElementById('lb-view-office');
        p.classList.remove('hidden'); p.style.display = 'flex';
        const iconColor = ext.includes('xls') ? '#16a34a' : '#2563eb';
        const iconBg    = ext.includes('xls') ? '#f0fdf4' : '#eff6ff';
        const appName   = ext.includes('xls') ? 'Microsoft Excel' : 'Microsoft Word';
        document.getElementById('lb-office-inner').innerHTML = `
            <div class="w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:${iconBg}; color:${iconColor}">
                <span class="text-2xl font-bold">${ext.toUpperCase()}</span>
            </div>
            <p class="text-base font-semibold text-gray-800">${escHtml(file.name)}</p>
            <p class="text-sm text-gray-400 mt-1">${formatSize(file.size)}</p>
            <p class="text-xs text-gray-400 mt-3 max-w-xs">Browser preview not supported for ${ext.toUpperCase()} files. Download to open in ${appName}.</p>
            <a href="${officeUrl}" download="${escHtml(file.name)}"
               class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white rounded-xl" style="background:${iconColor}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Download &amp; Open
            </a>`;
    } else if (ext === 'zip') {
        const p = document.getElementById('lb-view-zip');
        p.classList.remove('hidden');
        const listEl = document.getElementById('lb-zip-list');
        listEl.innerHTML = '<p class="text-xs text-gray-400">Reading archive…</p>';
        loadJSZip().then(JSZip => {
            JSZip.loadAsync(file).then(zip => {
                const entries = Object.values(zip.files).sort((a, b) => {
                    if (a.dir !== b.dir) return a.dir ? -1 : 1;
                    return a.name.localeCompare(b.name);
                });
                listEl.innerHTML = entries.length === 0
                    ? '<p class="text-xs text-gray-400 italic">Archive is empty.</p>'
                    : entries.map(e => {
                        const icon = e.dir
                            ? `<svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>`
                            : `<svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`;
                        const sz = (!e.dir && e._data?.uncompressedSize) ? `<span class="text-[11px] text-gray-400 ml-auto">${formatSize(e._data.uncompressedSize)}</span>` : '';
                        return `<div class="flex items-center gap-2 py-1 px-2 rounded hover:bg-gray-50">${icon}<span class="text-xs ${e.dir?'font-semibold text-gray-700':'text-gray-600'} truncate">${escHtml(e.name)}</span>${sz}</div>`;
                    }).join('');
            }).catch(() => { listEl.innerHTML = '<p class="text-xs text-red-500">Could not read archive.</p>'; });
        });
    } else {
        const p = document.getElementById('lb-view-generic');
        p.classList.remove('hidden'); p.style.display = 'flex';
        const meta = getFileMeta(file);
        const icon = document.getElementById('lb-generic-icon');
        icon.textContent = meta.label;
        icon.style.background = meta.bg;
        icon.style.color = meta.color;
    }
}

function closeLightbox() {
    document.getElementById('lightbox').classList.add('hidden');
    document.body.style.overflow = '';
    document.getElementById('lb-pdf-frame').src = '';
    currentLightboxId = null;
}

function navigateLightbox(dir) {
    const idx = selectedFiles.findIndex(f => f.id === currentLightboxId);
    const next = idx + dir;
    if (next < 0 || next >= selectedFiles.length) return;
    currentLightboxId = selectedFiles[next].id;
    loadIntoLightbox(selectedFiles[next]);
    updateNavState();
}

function updateNavState() {
    const idx = selectedFiles.findIndex(f => f.id === currentLightboxId);
    document.getElementById('lb-prev').disabled = idx === 0;
    document.getElementById('lb-next').disabled = idx === selectedFiles.length - 1;
    document.getElementById('lb-nav-label').textContent = `${idx + 1} of ${selectedFiles.length}`;
}

// ─── Lazy-load JSZip ──────────────────────────────────────────────────────────
let _jszip = null;
function loadJSZip() {
    if (_jszip) return _jszip;
    _jszip = new Promise((res, rej) => {
        const s = document.createElement('script');
        s.src = 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js';
        s.onload = () => res(window.JSZip);
        s.onerror = rej;
        document.head.appendChild(s);
    });
    return _jszip;
}

// ─── Keyboard shortcuts ───────────────────────────────────────────────────────
document.addEventListener('keydown', e => {
    if (e.key === 'Escape')     closeLightbox();
    if (e.key === 'ArrowLeft')  navigateLightbox(-1);
    if (e.key === 'ArrowRight') navigateLightbox(1);
});

// ─── Form submit ──────────────────────────────────────────────────────────────
document.getElementById('ticket-form').addEventListener('submit', function () {
    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.innerHTML = `<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg> Submitting…`;
});
</script>
@endpush