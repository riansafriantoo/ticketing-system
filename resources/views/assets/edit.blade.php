@extends('layouts.app')
@section('title', 'Edit ' . $asset->asset_tag)
@section('heading', 'Edit Asset')
@section('subheading', $asset->asset_tag . ' — ' . $asset->name)

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('assets.update', $asset) }}" enctype="multipart/form-data" class="space-y-5">
        @csrf @method('PUT')

        {{-- ── Identity ─────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Asset Identity</h3>
            <div class="grid grid-cols-2 gap-4">

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Asset Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $asset->name) }}"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200 @error('name') border-red-400 @enderror">
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Category <span class="text-red-500">*</span></label>
                    <select name="category" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:border-brand-400">
                        @foreach($categories as $c)
                        <option value="{{ $c->value }}" {{ old('category', $asset->category->value) === $c->value ? 'selected' : '' }}>
                            {{ $c->icon() }} {{ $c->label() }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Status <span class="text-red-500">*</span></label>
                    <select name="status" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:border-brand-400">
                        @foreach($statuses as $s)
                        <option value="{{ $s->value }}" {{ old('status', $asset->status->value) === $s->value ? 'selected' : '' }}>
                            {{ $s->label() }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Brand</label>
                    <input type="text" name="brand" value="{{ old('brand', $asset->brand) }}"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Model</label>
                    <input type="text" name="model" value="{{ old('model', $asset->model) }}"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400">
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Serial Number</label>
                    <input type="text" name="serial_number" value="{{ old('serial_number', $asset->serial_number) }}"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 @error('serial_number') border-red-400 @enderror">
                    @error('serial_number')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 resize-none focus:outline-none focus:border-brand-400">{{ old('description', $asset->description) }}</textarea>
                </div>
            </div>
        </div>

        {{-- ── Purchase & Warranty ──────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Purchase & Warranty</h3>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Purchase Date</label>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date', $asset->purchase_date?->format('Y-m-d')) }}"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Purchase Cost ($)</label>
                    <input type="number" name="purchase_cost" value="{{ old('purchase_cost', $asset->purchase_cost) }}"
                           step="0.01" min="0"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Warranty Expiry</label>
                    <input type="date" name="warranty_expiry" value="{{ old('warranty_expiry', $asset->warranty_expiry?->format('Y-m-d')) }}"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400">
                </div>
            </div>
        </div>

        {{-- ── Location & Notes ──────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Location & Notes</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Location</label>
                    <input type="text" name="location" value="{{ old('location', $asset->location) }}"
                           placeholder="e.g. Office 1A, Server Room"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                    <textarea name="notes" rows="2"
                              class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 resize-none focus:outline-none focus:border-brand-400">{{ old('notes', $asset->notes) }}</textarea>
                </div>
            </div>
        </div>

        {{-- ── Image ────────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Asset Photo</h3>

            @if($asset->imageUrl())
            <div class="mb-3 flex items-center gap-3">
                <img src="{{ $asset->imageUrl() }}" class="h-20 rounded-lg object-contain border border-gray-100" alt="">
                <p class="text-xs text-gray-400">Current photo. Upload a new one to replace it.</p>
            </div>
            @endif

            <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-gray-200 rounded-lg cursor-pointer hover:border-brand-300 hover:bg-brand-50 transition-colors">
                <svg class="w-5 h-5 text-gray-300 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-xs text-gray-400">Click to upload new photo</p>
                <input type="file" name="image" accept="image/*" class="hidden" onchange="previewImage(event)">
            </label>
            <img id="image-preview" src="" alt="" class="hidden mt-3 h-24 rounded-lg object-contain border border-gray-100">
        </div>

        {{-- ── Actions ──────────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('assets.show', $asset) }}"
               class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
                ← Back
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function previewImage(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => {
        const img = document.getElementById('image-preview');
        img.src = ev.target.result;
        img.classList.remove('hidden');
    };
    reader.readAsDataURL(file);
}
</script>
@endpush