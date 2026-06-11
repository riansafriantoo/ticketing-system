@extends('layouts.app')
@section('title', 'My Profile')
@section('heading', 'My Profile')
@section('subheading', 'Manage your account details')

@section('content')
<div class="max-w-xl space-y-5">

    {{-- Avatar + name header --}}
    <div class="bg-white rounded-xl border border-gray-100 p-5 flex items-center gap-4">
        <img src="{{ $user->avatarUrl() }}" class="w-16 h-16 rounded-full" alt="">
        <div>
            <p class="text-base font-semibold text-gray-900">{{ $user->name }}</p>
            <p class="text-sm text-gray-400">{{ $user->email }}</p>
            <span class="inline-flex mt-1 px-2 py-0.5 bg-brand-50 text-brand-700 text-[11px] font-medium rounded-md capitalize">
                {{ $user->roles->first()?->name ?? 'user' }}
            </span>
        </div>
    </div>

    {{-- Edit form --}}
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Personal Information</h3>
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf @method('PATCH')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Full name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400 @error('name') border-red-400 @enderror">
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Company <span class="text-red-500">*</span></label>
                    <input type="text" name="department" value="{{ old('department', $user->department) }}"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                       class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-400">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Avatar <span class="text-gray-300">(jpg/png, max 2 MB)</span></label>
                <input type="file" name="avatar" accept="image/*"
                       class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-gray-100 file:text-xs file:font-medium file:text-gray-700 hover:file:bg-gray-200">
                @error('avatar')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="flex justify-end pt-1">
                <button type="submit"
                        class="px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    {{-- Stats --}}
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Activity</h3>
        <div class="grid grid-cols-3 gap-4 text-center">
            <div>
                <p class="text-xl font-semibold text-gray-900">{{ $user->submittedTickets()->count() }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Tickets submitted</p>
            </div>
            @if($user->isAgent())
            <div>
                <p class="text-xl font-semibold text-gray-900">{{ $user->assignedTickets()->count() }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Tickets assigned</p>
            </div>
            <div>
                <p class="text-xl font-semibold text-green-600">
                    {{ $user->assignedTickets()->whereNotNull('resolved_at')->count() }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">Resolved</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection