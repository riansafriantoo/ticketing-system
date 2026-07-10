<?php

namespace App\Http\Controllers;

use App\Enums\TicketCategory;
use App\Enums\TicketCaseType;
use App\Enums\TicketPriority;
use App\Enums\TicketPriorityRequester;
use App\Enums\TicketStatus;
use App\Enums\TicketStatusNew;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Http\Requests\TransitionTicketRequest;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TicketController extends Controller
{
    use AuthorizesRequests;
    
    public function __construct(
        private readonly TicketService $service
    ) {}    

    public function index(Request $request): View
    {
        $user = $request->user();

        $selectedDepartment = $user->department;
        if($user->isAgent()){
            $query = Ticket::with(['requester', 'assignee'])
                ->when(!$user->isAgent(), fn ($q) => $q->forRequester($user->id))
                ->when($request->filled('status'),   fn ($q) => $q->where('status', $request->status))
                ->when($request->filled('priority'),  fn ($q) => $q->where('priority', $request->priority))
                ->when($request->filled('requester'),  fn ($q) => $q->where('requester_id', $request->requester))
                ->when($request->filled('category'),  fn ($q) => $q->where('category', $request->category))
                ->when($request->filled('assignee'),  fn ($q) => $q->where('assignee_id', $request->assignee))
                ->when($request->filled('search'),    fn ($q) => $q->search($request->search))
                ->when($request->boolean('overdue'),  fn ($q) => $q->overdue())
                ->latest();
        }else{
            $query = Ticket::with(['requester', 'assignee'])
                ->when(!$user->isAgent(), fn ($q) => $q->forRequester($user->id))
                ->when($request->filled('status'),   fn ($q) => $q->where('status', $request->status))
                ->when($request->filled('priority'),  fn ($q) => $q->where('priority', $request->priority))
                ->when($request->filled('category'),  fn ($q) => $q->where('category', $request->category))
                ->when($request->filled('search'),    fn ($q) => $q->search($request->search))
                ->when($request->boolean('overdue'),  fn ($q) => $q->overdue())
                ->when(filled($selectedDepartment),   fn ($q) => $q
                    ->join('users', 'tickets.requester_id', '=', 'users.id')
                    ->where('users.department', $selectedDepartment)
                    ->select('tickets.*')
                )
                ->latest();
        }

        $tickets = $query->paginate(config('it-ticketing.tickets_per_page', 20))
                         ->withQueryString();

        return view('tickets.index', [
            'tickets'    => $tickets,
            'statuses'   => TicketStatus::cases(),
            'statusesNew' => TicketStatusNew::cases(),
            'priorities' => TicketPriority::cases(),
            'prioritiesRequester' => TicketPriorityRequester::cases(),
            'categories' => TicketCategory::cases(),
            'caseTypes'  => TicketCaseType::cases(),
            'agents'     => User::role(['agent', 'admin'])->where('is_active', '1')->orderBy('name')->get(),
            'requesters' => User::where('is_active', '1')->orderBy('name')->get(),
            'metrics'    => $user->isAgent() ? $this->service->metrics($user) : $this->service->metricsRequester($user),
        ]);
    }

    public function create(): View
    {
        return view('tickets.create', [
            'priorities' => TicketPriority::cases(),
            'prioritiesRequester' => TicketPriorityRequester::cases(),
            'agents'  => User::role(['agent', 'admin'])->where('is_active', '1')->orderBy('name')->get(),
            'categories' => TicketCategory::cases(),
            'caseTypes' => TicketCaseType::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject'     => 'required|string|max:255',
            'description' => 'required|string|max:20000',
            'priority'    => 'required|in:' . implode(',', TicketPriority::values()),
            'asset_id'    => 'nullable|exists:assets,id',
        ]);
 
        $request->validate([
            'attachments'   => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt,zip',
        ]);
 
        $attachments = $request->file('attachments') ?? [];
 
        $ticket = $this->service->create($validated, $attachments, $request->user());
 
        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', "Ticket {$ticket->ticketNumber()} created successfully.");
    }

    public function show(Ticket $ticket): View
    {
        // $this->authorize('view', $ticket);

        $ticket->load(['requester', 'assignee', 'comments.user','comments.attachments', 'attachments.user', 'activities.user']);

        return view('tickets.show', [
            'ticket'  => $ticket,
            'agents'  => User::role(['agent', 'admin'])->where('is_active', '1')->orderBy('name')->get(),
            'statuses'=> $ticket->status->transitions(),
        ]);
    }

    public function edit(Ticket $ticket): View
    {
        $this->authorize('update', $ticket);

        return view('tickets.edit', [
            'ticket'     => $ticket,
            'priorities' => TicketPriority::cases(),
            'prioritiesRequester' => TicketPriorityRequester::cases(),
            'caseTypes' => TicketCaseType::cases(),
        ]);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->service->update($ticket, $request->validated(), $request->user());

        return back()->with('success', 'Ticket updated.');
    }

    public function transition(TransitionTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('transition', $ticket);

        try {
            $this->service->transition(
                $ticket,
                TicketStatusNew::from($request->validated('status')),
                $request->user()
            );
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Ticket status updated.');
    }

    public function assign(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('assign', $ticket);

        $request->validate(['assignee_id' => 'nullable|exists:users,id']);

        $assignee = $request->filled('assignee_id') ? User::findOrFail($request->assignee_id) : null;

        $this->service->assign($ticket, $assignee, $request->user());

        return back()->with('success', 'Ticket assigned.');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        $this->authorize('delete', $ticket);
        $ticket->delete();

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket deleted.');
    }
}