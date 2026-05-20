<?php

namespace App\Http\Controllers;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AdminController extends Controller
{
    use AuthorizesRequests;
    
    public function __construct(
        private readonly TicketService $service
    ) {}

    public function dashboard(): View
    {
        $metrics = $this->service->metrics();

        $byStatus = Ticket::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $byPriority = Ticket::selectRaw('priority, COUNT(*) as count')
            ->whereNotIn('status', [TicketStatus::Closed])
            ->groupBy('priority')
            ->pluck('count', 'priority');

        $agentLoad = User::role(['agent', 'admin'])
            ->withCount(['assignedTickets as open_count' => function ($q) {
                $q->whereNotIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value]);
            }])
            ->orderByDesc('open_count')
            ->take(10)
            ->get();

        $recentTickets = Ticket::with(['requester', 'assignee'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'metrics', 'byStatus', 'byPriority', 'agentLoad', 'recentTickets'
        ));
    }

    public function users(): View
    {
        $users = User::with('roles')
            ->withCount(['submittedTickets', 'assignedTickets'])
            ->orderBy('name')
            ->paginate(25);

        return view('admin.users', compact('users'));
    }

    public function updateUserRole(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $request->validate(['role' => 'required|in:user,agent,admin']);
        $user->syncRoles([$request->role]);

        return back()->with('success', "Role updated to {$request->role}.");
    }
}