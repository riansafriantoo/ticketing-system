<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketCreated;
use App\Notifications\TicketStatusChanged;
use App\Notifications\TicketAssigned;
use App\Notifications\SlaBreaching;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    public function ticketCreated(Ticket $ticket): void
    {
        $this->safely(function () use ($ticket) {
            // Notify requester
            $ticket->requester->notify(new TicketCreated($ticket));

            // Notify all admins
            $admins = User::role('admin')->get();
            Notification::send($admins, new TicketCreated($ticket));
        });
    }

    public function statusChanged(Ticket $ticket, TicketStatus $oldStatus): void
    {
        $this->safely(function () use ($ticket, $oldStatus) {
            $ticket->requester->notify(new TicketStatusChanged($ticket, $oldStatus));
        });
    }

    public function ticketAssigned(Ticket $ticket, User $assignee): void
    {
        $this->safely(function () use ($ticket, $assignee) {
            $assignee->notify(new TicketAssigned($ticket));
        });
    }

    public function slaBreaching(Ticket $ticket): void
    {
        $this->safely(function () use ($ticket) {
            $recipients = collect([$ticket->assignee, $ticket->requester])
                ->filter()
                ->unique('id');

            $admins = User::role('admin')->get();
            $all    = $recipients->merge($admins)->unique('id');

            Notification::send($all, new SlaBreaching($ticket));
        });
    }

    private function safely(callable $fn): void
    {
        try {
            $fn();
        } catch (\Throwable $e) {
            Log::warning("Notification failed: {$e->getMessage()}");
        }
    }
}