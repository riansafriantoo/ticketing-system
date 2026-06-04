<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Services\TicketService;

class AdminUserController extends Controller
{
    use AuthorizesRequests;
    
    public function __construct(
        private readonly TicketService $service
    ) {}   

    // ── List all users ────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = User::with('roles')
            ->withCount(['submittedTickets', 'assignedTickets'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('department', 'like', "%{$request->search}%");
            })
            ->when($request->filled('role'), function ($q) use ($request) {
                $q->role($request->role);
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('is_active', $request->status === 'active');
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $query,
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    // ── Show create form ──────────────────────────────────────────────────────

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    // ── Store new user ────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'   => ['required', 'confirmed', Password::min(8)
                                ->mixedCase()
                                ->numbers()],
            'role'       => ['required', 'string', 'exists:roles,name'],
            'department' => ['nullable', 'string', 'max:100'],
            'phone'      => ['nullable', 'string', 'max:30'],
            'is_active'  => ['boolean'],
        ]);

        $user = User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
            'department' => $data['department'] ?? null,
            'phone'      => $data['phone']       ?? null,
            'is_active'  => $data['is_active']   ?? true,
        ]);

        $user->assignRole($data['role']);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User \"{$user->name}\" created successfully.");
    }

    // ── Show single user ──────────────────────────────────────────────────────

    public function show(User $user): View
    {
        $user->load('roles')
             ->loadCount(['submittedTickets', 'assignedTickets']);

        $recentTickets = $user->submittedTickets()
            ->with('assignee')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.users.show', compact('user', 'recentTickets'));
    }

    // ── Show edit form ────────────────────────────────────────────────────────

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user'  => $user->load('roles'),
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    // ── Update user ───────────────────────────────────────────────────────────

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'password'   => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'role'       => ['required', 'string', 'exists:roles,name'],
            'department' => ['nullable', 'string', 'max:100'],
            'phone'      => ['nullable', 'string', 'max:30'],
            'is_active'  => ['boolean'],
        ]);

        $user->update([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'department' => $data['department'] ?? null,
            'phone'      => $data['phone']       ?? null,
            'is_active'  => $data['is_active']   ?? true,
            ...($data['password'] ? ['password' => Hash::make($data['password'])] : []),
        ]);

        $user->syncRoles([$data['role']]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User \"{$user->name}\" updated successfully.");
    }

    // ── Toggle active status ──────────────────────────────────────────────────

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $label = $user->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "User \"{$user->name}\" {$label}.");
    }

    // ── Hard delete ───────────────────────────────────────────────────────────

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User \"{$name}\" deleted.");
    }
}