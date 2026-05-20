<?php

namespace App\Jobs;

use App\Enums\TicketStatus;
use App\Models\Activity;
use App\Models\Ticket;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckSlaBreaches implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(NotificationService $notifier): void
    {
        // Find newly breached tickets (breached but not yet flagged)
        $breached = Ticket::whereNotIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value])
            ->where('sla_due_at', '<', now())
            ->where('sla_breached', false)
            ->with(['requester', 'assignee'])
            ->get();

        foreach ($breached as $ticket) {
            try {
                $ticket->update(['sla_breached' => true]);

                Activity::create([
                    'ticket_id'  => $ticket->id,
                    'user_id'    => null,
                    'action'     => 'sla_breached',
                    'meta'       => ['breached_at' => now()->toIso8601String()],
                    'created_at' => now(),
                ]);

                $notifier->slaBreaching($ticket);

                Log::info("SLA breached for ticket #{$ticket->id} ({$ticket->ticketNumber()})");
            } catch (\Throwable $e) {
                Log::error("SLA breach processing failed for ticket #{$ticket->id}: {$e->getMessage()}");
            }
        }

        // Find tickets approaching SLA (within 1 hour, not yet breached)
        $warning = Ticket::whereNotIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value])
            ->where('sla_due_at', '>', now())
            ->where('sla_due_at', '<=', now()->addHour())
            ->where('sla_breached', false)
            ->with(['requester', 'assignee'])
            ->get();

        foreach ($warning as $ticket) {
            try {
                $notifier->slaBreaching($ticket);
            } catch (\Throwable $e) {
                Log::warning("SLA warning notification failed for ticket #{$ticket->id}: {$e->getMessage()}");
            }
        }

        Log::info("SLA check complete — breached: {$breached->count()}, warnings: {$warning->count()}");
    }
}