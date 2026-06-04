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

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Department</label>
                    <input type="text" name="department" value="{{ old('department', $user->department) }}"
                           placeholder="e.g. Finance, HR, IT"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200">
                </div>
            </div>
        </div>

        {{-- ── Role ─────────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Role</h3>
            <div class="space-y-2">
                @foreach($roles as $role)
                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:border-brand-300 hover:bg-brand-50 transition-colors">
                    <input type="radio" name="role" value="{{ $role->name }}"
                           {{ old('role', $user->roles->first()?->name) === $role->name ? 'checked' : '' }}
                           class="text-brand-600 focus:ring-brand-300">
                    <div>
                        <p class="text-sm font-medium text-gray-900 capitalize">{{ $role->name }}</p>
                        <p class="text-xs text-gray-400">
                            @if($role->name === 'admin') Full system access
                            @elseif($role->name === 'agent') Support staff — manage all tickets
                            @else End user — submit and track own tickets
                            @endif
                        </p>
                    </div>
                </label>
                @endforeach
            </div>
            @error('role')<p class="mt-2 text-xs text-red-500">{{ $message }}</p>@enderror
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