@extends('layouts.app')
@section('title', 'Users')
@section('heading', 'Users')
@section('subheading', $users->total() . ' registered users')

@section('content')
<div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
    <table class="min-w-full">
        <thead>
            <tr class="border-b border-gray-100">
                <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">User</th>
                <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Department</th>
                <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-24">Role</th>
                <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-24 text-center">Submitted</th>
                <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-24 text-center">Assigned</th>
                <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-20">Joined</th>
                <th class="px-4 py-3 w-36"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($users as $user)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <img src="{{ $user->avatarUrl() }}" class="w-8 h-8 rounded-full flex-shrink-0" alt="">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                            <p class="text-xs text-gray-400">{{ $user->email }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <span class="text-sm text-gray-600">{{ $user->department ?? '—' }}</span>
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex px-2 py-0.5 rounded-md text-[11px] font-medium
                        @if($user->hasRole('admin')) bg-purple-100 text-purple-800
                        @elseif($user->hasRole('agent')) bg-blue-100 text-blue-800
                        @else bg-gray-100 text-gray-600 @endif
                        capitalize">
                        {{ $user->roles->first()?->name ?? 'user' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="text-sm text-gray-700">{{ $user->submitted_tickets_count }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="text-sm text-gray-700">{{ $user->assigned_tickets_count }}</span>
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs text-gray-400">{{ $user->created_at->format('M Y') }}</span>
                </td>
                <td class="px-4 py-3">
                    @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.role', $user) }}" class="flex items-center gap-1.5">
                        @csrf @method('PATCH')
                        <select name="role"
                                class="text-xs border border-gray-200 rounded-md px-2 py-1 bg-white focus:outline-none focus:border-brand-400">
                            <option value="user"  {{ $user->hasRole('user')  ? 'selected' : '' }}>User</option>
                            <option value="agent" {{ $user->hasRole('agent') ? 'selected' : '' }}>Agent</option>
                            <option value="admin" {{ $user->hasRole('admin') ? 'selected' : '' }}>Admin</option>
                        </select>
                        <button type="submit"
                                class="px-2 py-1 text-xs bg-brand-600 text-white rounded-md hover:bg-brand-700">
                            Save
                        </button>
                    </form>
                    @else
                    <span class="text-xs text-gray-300 italic">You</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($users->hasPages())
    <div class="px-4 py-3 border-t border-gray-100">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection