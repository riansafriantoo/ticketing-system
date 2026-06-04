@extends('layouts.app')

@section('title', 'Manage Users')
@section('heading', 'Users')
@section('subheading', $users->total() . ' registered users')

@section('header-actions')
<a href="{{ route('admin.users.create') }}"
   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-600 text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-700">
    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
    </svg>
    Add New User
</a>
@endsection

@section('content')
<div class="space-y-4">

    {{-- ── Filters ─────────────────────────────────────────────────────────── --}}
    <form method="GET" class="flex flex-wrap items-center gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search name, email, department…"
               class="w-64 text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-200">

        <select name="role" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus:outline-none focus:border-brand-400">
            <option value="">All roles</option>
            @foreach($roles as $role)
            <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                {{ ucfirst($role->name) }}
            </option>
            @endforeach
        </select>

        <select name="status" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus:outline-none focus:border-brand-400">
            <option value="">All statuses</option>
            <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>

        <button type="submit"
                class="px-3 py-1.5 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
            Filter
        </button>

        @if(request()->hasAny(['search', 'role', 'status']))
        <a href="{{ route('admin.users.index') }}"
           class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg">
            Clear
        </a>
        @endif
    </form>

    {{-- ── Table ───────────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        @if($users->isEmpty())
        <div class="flex flex-col items-center justify-center py-20">
            <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500">No users found</p>
            <p class="text-xs text-gray-400 mt-1">
                Try adjusting your filters or
                <a href="{{ route('admin.users.create') }}" class="text-brand-600 underline">add a new user</a>
            </p>
        </div>
        @else
        <table class="min-w-full">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-5 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">User</th>
                    <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Department</th>
                    <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-24">Role</th>
                    <th class="text-center px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-24">Tickets</th>
                    <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-24">Status</th>
                    <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-24">Joined</th>
                    <th class="px-4 py-3 w-28"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($users as $user)
                <tr class="hover:bg-gray-50 group">
                    {{-- User info --}}
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $user->avatarUrl() }}" class="w-8 h-8 rounded-full flex-shrink-0" alt="">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                <p class="text-xs text-gray-400">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- Department --}}
                    <td class="px-4 py-3">
                        <span class="text-sm text-gray-600">{{ $user->department ?? '—' }}</span>
                    </td>

                    {{-- Role badge --}}
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded-md text-[11px] font-semibold capitalize
                            @if($user->hasRole('admin'))  bg-purple-100 text-purple-800
                            @elseif($user->hasRole('agent')) bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-600
                            @endif">
                            {{ $user->roles->first()?->name ?? 'user' }}
                        </span>
                    </td>

                    {{-- Ticket count --}}
                    <td class="px-4 py-3 text-center">
                        <span class="text-sm text-gray-700">{{ $user->submitted_tickets_count }}</span>
                    </td>

                    {{-- Active status --}}
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium
                            {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-green-500' : 'bg-red-400' }}"></span>
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>

                    {{-- Joined date --}}
                    <td class="px-4 py-3">
                        <span class="text-xs text-gray-400">{{ $user->created_at->format('M d, Y') }}</span>
                    </td>

                    {{-- Actions --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            {{-- View --}}
                            <a href="{{ route('admin.users.show', $user) }}"
                               title="View"
                               class="p-1.5 rounded-md text-gray-400 hover:text-brand-600 hover:bg-brand-50">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>

                            {{-- Edit --}}
                            <a href="{{ route('admin.users.edit', $user) }}"
                               title="Edit"
                               class="p-1.5 rounded-md text-gray-400 hover:text-amber-600 hover:bg-amber-50">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>

                            {{-- Toggle active --}}
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}"
                                        class="p-1.5 rounded-md text-gray-400 {{ $user->is_active ? 'hover:text-red-500 hover:bg-red-50' : 'hover:text-green-600 hover:bg-green-50' }}">
                                    @if($user->is_active)
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                    @else
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    @endif
                                </button>
                            </form>

                            {{-- Delete --}}
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                  onsubmit="return confirm('Delete {{ addslashes($user->name) }}? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        title="Delete"
                                        class="p-1.5 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($users->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">
            {{ $users->links() }}
        </div>
        @endif
        @endif
    </div>
</div>
@endsection