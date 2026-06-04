@extends('layouts.app')

@section('title', 'Add New User')
@section('heading', 'Add New User')
@section('subheading', 'Create a new user account and assign their role')

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('users.store') }}" class="space-y-5" id="create-user-form">
        @csrf

        {{-- ── Personal Information ────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-5">
                Personal Information
            </h3>

            <div class="grid grid-cols-2 gap-4">

                {{-- Full name --}}
                <div class="col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name') }}"
                           placeholder="e.g. John Smith"
                           autofocus
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200 @error('name') border-red-400 bg-red-50 @enderror">
                    @error('name')
                    <p class="mt-1 text-xs text-red-500 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="col-span-2">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email') }}"
                           placeholder="john@company.com"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200 @error('email') border-red-400 bg-red-50 @enderror">
                    @error('email')
                    <p class="mt-1 text-xs text-red-500 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                {{-- Department --}}
                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Department <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <input type="text" id="department" name="department"
                           value="{{ old('department') }}"
                           placeholder="e.g. Finance, HR, IT"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200">
                </div>

                {{-- Phone --}}
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Phone <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <input type="text" id="phone" name="phone"
                           value="{{ old('phone') }}"
                           placeholder="+62 812 3456 7890"
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

        {{-- ── Password ─────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-5">
                Password
            </h3>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password"
                               class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 pr-9 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200 @error('password') border-red-400 bg-red-50 @enderror">
                        <button type="button" onclick="togglePassword('password')"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" id="eye-password" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Confirm Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 pr-9 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200">
                        <button type="button" onclick="togglePassword('password_confirmation')"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Password strength rules --}}
            <div class="mt-3 flex flex-wrap gap-2" id="password-rules">
                <span class="rule flex items-center gap-1 text-[11px] text-gray-400" id="rule-length">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                    Min 8 characters
                </span>
                <span class="rule flex items-center gap-1 text-[11px] text-gray-400" id="rule-upper">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                    Uppercase letter
                </span>
                <span class="rule flex items-center gap-1 text-[11px] text-gray-400" id="rule-lower">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                    Lowercase letter
                </span>
                <span class="rule flex items-center gap-1 text-[11px] text-gray-400" id="rule-number">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                    Number
                </span>
            </div>
        </div>

        {{-- ── Account Status ───────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">
                Account Status
            </h3>
            <label class="flex items-center justify-between cursor-pointer">
                <div>
                    <p class="text-sm font-medium text-gray-700">Active account</p>
                    <p class="text-xs text-gray-400 mt-0.5">User can log in and submit tickets immediately</p>
                </div>
                <div class="relative">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', '1') === '1' ? 'checked' : '' }}
                           class="sr-only peer" id="toggle-active">
                    <div class="w-10 h-6 bg-gray-200 peer-checked:bg-brand-600 rounded-full transition-colors peer-focus:ring-2 peer-focus:ring-brand-300"></div>
                    <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                </div>
            </label>
        </div>

        {{-- ── Submit ───────────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('users.index') }}"
               class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
                ← Cancel
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-300">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Create User
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// Toggle password visibility
function togglePassword(fieldId) {
    const input = document.getElementById(fieldId);
    input.type = input.type === 'password' ? 'text' : 'password';
}

// Live password strength indicators
const passwordInput = document.getElementById('password');
const rules = {
    'rule-length': v => v.length >= 8,
    'rule-upper':  v => /[A-Z]/.test(v),
    'rule-lower':  v => /[a-z]/.test(v),
    'rule-number': v => /[0-9]/.test(v),
};

const checkSvg   = `<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>`;
const circleSvg  = `<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>`;

passwordInput.addEventListener('input', function () {
    const val = this.value;
    for (const [id, test] of Object.entries(rules)) {
        const el = document.getElementById(id);
        if (test(val)) {
            el.className = 'rule flex items-center gap-1 text-[11px] text-green-600';
            el.innerHTML = checkSvg + el.textContent.trim();
        } else {
            el.className = 'rule flex items-center gap-1 text-[11px] text-gray-400';
            el.innerHTML = circleSvg + el.textContent.trim();
        }
    }
});

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