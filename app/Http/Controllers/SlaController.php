<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Enums\TicketStatus;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SlaController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Ticket::with(['requester:id,name', 'assignee:id,name'])
            ->whereNotIn('status', [
                TicketStatus::Resolved->value,
                TicketStatus::Closed->value,
            ])
            ->where('sla_due_at', '<', now());

        // Regular users only see their own overdue tickets
        if (! $user->isAgent()) {
            $query->where('requester_id', $user->id);
        }

        $tickets = $query->orderBy('sla_due_at')->get();

        $payload = $tickets->map(function (Ticket $ticket) {
            return [
                'id'             => $ticket->id,
                'ticket_number'  => $ticket->ticketNumber(),
                'subject'        => $ticket->subject,
                'priority'       => $ticket->priority->value,
                'priority_label' => $ticket->priority->label(),
                'status'         => $ticket->status->value,
                'status_label'   => $ticket->status->label(),
                'requester'      => $ticket->requester?->name,
                'assignee'       => $ticket->assignee?->name ?? 'Unassigned',
                'sla_due_at'     => $ticket->sla_due_at?->toIso8601String(),
                'overdue_since'  => $ticket->sla_due_at?->diffForHumans(),
                'url'            => route('tickets.show', $ticket),
            ];
        });

        return response()->json([
            'count'   => $tickets->count(),
            'tickets' => $payload,
        ]);
    }

    /**
     * Dismiss a single ticket's alert for the current session.
     * The ticket is still overdue — we just suppress the popup for 30 min.
     */
    public function dismiss(Request $request, int $ticketId): JsonResponse
    {
        $request->validate(['ticket_id' => 'sometimes|integer']);

        $cacheKey = "sla_dismissed_{$request->user()->id}_{$ticketId}";
        Cache::put($cacheKey, true, now()->addMinutes(30));

        return response()->json(['dismissed' => true]);
    }

    /**
     * Dismiss all current overdue alerts for 30 minutes.
     */
    public function dismissAll(Request $request): JsonResponse
    {
        $user = $request->user();

        $overdueIds = Ticket::whereNotIn('status', [
                TicketStatus::Resolved->value,
                TicketStatus::Closed->value,
            ])
            ->where('sla_due_at', '<', now())
            ->when(! $user->isAgent(), fn ($q) => $q->where('requester_id', $user->id))
            ->pluck('id');

        foreach ($overdueIds as $id) {
            Cache::put("sla_dismissed_{$user->id}_{$id}", true, now()->addMinutes(30));
        }

        return response()->json(['dismissed_count' => $overdueIds->count()]);
    }
    
    public function check()
    {
        $tickets = Ticket::overdue()
            ->where('sla_breached', false)
            ->with(['requester', 'assignee'])
            ->get();

        if ($tickets->count() > 0) {

            // tandai sudah breach
            Ticket::whereIn('id', $tickets->pluck('id'))
                ->update([
                    'sla_breached' => true
                ]);
        }

        return response()->json([
            'count' => $tickets->count(),
            'tickets' => $tickets->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'number' => $ticket->ticketNumber(),
                    'subject' => $ticket->subject,
                    'priority' => $ticket->priority->value,
                    'requester' => $ticket->requester?->name,
                    'assignee' => $ticket->assignee?->name,
                    'sla_due_at' => $ticket->sla_due_at?->format('Y-m-d H:i:s'),
                ];
            })
        ]);
    }
}