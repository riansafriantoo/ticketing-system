@extends('layouts.app')

@section('title', 'Edit User')
@section('heading', 'Edit User')
@section('subheading', $user->name . ' — ' . $user->email)

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-5">
        @csrf @method('PUT')

        {{-- ── Personal Information ─────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <div class="flex items-center gap-4 mb-5 pb-5 border-b border-gray-100">
                <img src="{{ $user->avatarUrl() }}" class="w-12 h-12 rounded-full" alt="">
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $user->name }}</p>
                    <p class="text-xs text-gray-400">Joined {{ $user->created_at->format('M d, Y') }}</p>
                </div>
            </div>

            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Personal Information</h3>

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200 @error('name') border-red-400 @enderror">
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200 @error('email') border-red-400 @enderror">
                    @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                {{-- Department --}}
                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Company <span class="text-red-500">*</span>
                    </label>
                    <select id="department" name="department" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200">
                        <option value="">Select company</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->value }}" {{ old('department') === $dept->value ? 'selected' : '' }}>
                            {{ $dept->label() }}
                        </option>
                        @endforeach
                    </select>
                    @error('department')
                    <p class="mt-1 text-xs text-red-500 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        {{ $message }}
                    </p>
                    @enderror
                    {{-- <input type="text" id="department" name="department" value="{{ old('department') }}" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200"> --}}
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200">
                </div>
            </div>
        </div>

        {{-- ── Role & Access ────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-5">
                Role & Access
            </h3>

            <div class="space-y-3">
                @foreach($roles as $role)
                <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:border-brand-300 hover:bg-brand-50 transition-colors
                    {{ old('role') === $role->name ? 'border-brand-400 bg-brand-50' : '' }}"
                    id="role-card-{{ $role->name }}">
                    <input type="radio" name="role" value="{{ $role->name }}"
                           {{ old('role', 'user') === $role->name ? 'checked' : '' }}
                           class="mt-0.5 text-brand-600 focus:ring-brand-300"
                           onchange="highlightRole()">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-gray-900 capitalize">{{ $role->name }}</span>
                            @if($role->name === 'admin')
                            <span class="px-1.5 py-0.5 bg-purple-100 text-purple-700 text-[10px] font-semibold rounded uppercase tracking-wide">Full Access</span>
                            @elseif($role->name === 'agent')
                            <span class="px-1.5 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-semibold rounded uppercase tracking-wide">Support</span>
                            @else
                            <span class="px-1.5 py-0.5 bg-gray-100 text-gray-500 text-[10px] font-semibold rounded uppercase tracking-wide">Default</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">
                            @if($role->name === 'admin')
                                Full system access — manage users, view all tickets, access dashboard and reports.
                            @elseif($role->name === 'agent')
                                Support staff — can view, assign, update, and resolve any ticket.
                            @else
                                End user — can submit tickets and track their own requests only.
                            @endif
                        </p>
                    </div>
                </label>
                @endforeach
            </div>

            @error('role')
            <p class="mt-2 text-xs text-red-500 flex items-center gap-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                {{ $message }}
            </p>
            @enderror
        </div>


        {{-- ── New Password (optional) ──────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">
                Change Password
            </h3>
            <p class="text-xs text-gray-400 mb-4">Leave blank to keep the current password.</p>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">New Password</label>
                    <input type="password" name="password"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200 @error('password') border-red-400 @enderror">
                    @error('password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm New Password</label>
                    <input type="password" name="password_confirmation"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200">
                </div>
            </div>
        </div>

        {{-- ── Account Status ───────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Account Status</h3>
            <label class="flex items-center justify-between cursor-pointer">
                <div>
                    <p class="text-sm font-medium text-gray-700">Active account</p>
                    <p class="text-xs text-gray-400 mt-0.5">Inactive users cannot log in</p>
                </div>
                <div class="relative">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $user->is_active ? '1' : '') === '1' ? 'checked' : '' }}
                           class="sr-only peer"
                           {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                    <div class="w-10 h-6 bg-gray-200 peer-checked:bg-brand-600 rounded-full transition-colors peer-focus:ring-2 peer-focus:ring-brand-300 peer-disabled:opacity-50"></div>
                    <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                </div>
            </label>
            @if($user->id === auth()->id())
            <p class="mt-2 text-xs text-amber-600">You cannot deactivate your own account.</p>
            @endif
        </div>

        {{-- ── Actions ──────────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('users.index') }}"
               class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
                ← Back to Users
            </a>
            <div class="flex items-center gap-2">
                <a href="{{ route('users.show', $user) }}"
                   class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
                    View Profile
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Changes
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// Highlight selected role card
function highlightRole() {
    document.querySelectorAll('[id^="role-card-"]').forEach(card => {
        const radio = card.querySelector('input[type="radio"]');
        if (radio.checked) {
            card.classList.add('border-brand-400', 'bg-brand-50');
            card.classList.remove('border-gray-200');
        } else {
            card.classList.remove('border-brand-400', 'bg-brand-50');
            card.classList.add('border-gray-200');
        }
    });
}
// Run on load to highlight default
highlightRole();
</script>
@endpush