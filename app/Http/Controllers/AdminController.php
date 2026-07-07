<?php

namespace App\Http\Controllers;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\Department;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AdminController extends Controller
{
    use AuthorizesRequests;
    
    public function __construct(
        private readonly TicketService $service
    ) {}

    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $metrics = $this->service->metrics($user);
        $metricsRequester = $this->service->metricsRequester($user);
        
        $byStatus = Ticket::selectRaw('status, COUNT(*) as count')
            ->whereNotIn('status', [TicketStatus::Resolved])
            ->groupBy('status')
            ->pluck('count', 'status');

        $byPriority = Ticket::selectRaw('priority, COUNT(*) as count')
            ->whereNotIn('status', [TicketStatus::Resolved])
            ->groupBy('priority')
            ->pluck('count', 'priority');

        $agentLoad = User::role(['agent', 'admin'])
            ->withCount(['assignedTickets as open_count' => function ($q) {
                $q->whereNotIn('status', [TicketStatus::Resolved->value]);
            }])
            ->orderByDesc('open_count')
            ->take(10)
            ->get();

        $selectedDepartment = $user->department;
 
        $recentTickets = Ticket::with(['requester', 'assignee'])
            ->when(
                filled($selectedDepartment),
                fn ($q) => $q
                    ->join('users', 'tickets.requester_id', '=', 'users.id')
                    ->where('users.department', $selectedDepartment)
                    ->whereIn('status', [TicketStatus::Open])
                    ->select('tickets.*')
            )
            ->latest('tickets.created_at')
            ->take(10)
            ->get();
                    

        if(auth()->user()->isAgent()) {
            return view('admin.dashboard', compact(
                'metrics', 'byStatus', 'byPriority', 'agentLoad', 'recentTickets', 'selectedDepartment'
            ));
        }else{
            return view('admin.dashboard_requester', compact(
                'metricsRequester', 'byStatus', 'byPriority', 'agentLoad', 'recentTickets', 'selectedDepartment'
            ));
        }
    }

    public function index(Request $request): View
    {
        $users = User::with('roles')
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

        $overdueTickets = Ticket::overdue()->get();

        return view('admin.users', [
            'users' => $users,
            'roles' => Role::orderBy('name')->get(),
            'overdueTickets' => $overdueTickets,
        ]);
    }

    public function create(): View
    {
        return view('admin.create', [
            'roles' => Role::orderBy('name')->get(),
            'departments' => Department::cases(),
        ]);
    }

    public function show(User $user): View
    {
        $user->load('roles')
             ->loadCount(['submittedTickets', 'assignedTickets']);

        $recentTickets = $user->submittedTickets()
            ->with('assignee')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.show', compact('user', 'recentTickets'));
    }

    // ── Show edit form ────────────────────────────────────────────────────────

    public function edit(User $user): View
    {
        return view('admin.edit', [
            'user'  => $user->load('roles'),
            'roles' => Role::orderBy('name')->get(),
            'departments' => Department::cases(),
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
            'department' => ['required', 'string', 'max:100'],
            'phone'      => ['nullable', 'string', 'max:30'],
            'is_active'  => ['boolean'],
        ]);

        $user = User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
            'department' => $data['department'],
            'phone'      => $data['phone']       ?? null,
            'is_active'  => $data['is_active']   ?? true,
        ]);

        $user->assignRole($data['role']);

        return redirect()
            ->route('users.index')
            ->with('success', "User \"{$user->name}\" created successfully.");
    }


    // ── Update user ───────────────────────────────────────────────────────────

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'password'   => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'role'       => ['required', 'string', 'exists:roles,name'],
            'department' => ['required', 'string', 'max:100'],
            'phone'      => ['nullable', 'string', 'max:30'],
            'is_active'  => ['boolean'],
        ]);

        $user->update([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'department' => $data['department'],
            'phone'      => $data['phone']       ?? null,
            'is_active'  => $data['is_active']   ?? true,
            ...($data['password'] ? ['password' => Hash::make($data['password'])] : []),
        ]);

        $user->syncRoles([$data['role']]);

        return redirect()
            ->route('users.index')
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

        $name = $user->name;

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }
 
        if (!$user->is_active) {
            return redirect()->route('admin.users.index')
                ->with('success', "User \"{$user->name}\" is already deactivated.");
        }
 
        $user->update(['is_active' => false]);

        return redirect()
            ->route('users.index')
            ->with('success', "User \"{$name}\" deactivated.");
    }

    public function reactivate(User $user): RedirectResponse
    {
        if ($user->is_active) {
            return back()->with('success', "User \"{$user->name}\" is already active.");
        }
 
        $user->update(['is_active' => true]);
 
        return back()->with('success', "User \"{$user->name}\" reactivated.");
    }

    public function updateUserRole(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $request->validate(['role' => 'required|in:user,agent,admin']);
        $user->syncRoles([$request->role]);

        return back()->with('success', "Role updated to {$request->role}.");
    }
}