<?php

namespace App\Notifications;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Ticket $ticket,
        public readonly TicketStatus $oldStatus
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[{$this->ticket->ticketNumber()}] Status Update: {$this->ticket->status->label()}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your ticket status has been updated.")
            ->line("**{$this->ticket->ticketNumber()}** — {$this->ticket->subject}")
            ->line("{$this->oldStatus->label()} → **{$this->ticket->status->label()}**")
            ->action('View Ticket', route('tickets.show', $this->ticket));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'ticket_id'    => $this->ticket->id,
            'ticket_number'=> $this->ticket->ticketNumber(),
            'subject'      => $this->ticket->subject,
            'message'      => "Ticket {$this->ticket->ticketNumber()} status changed to {$this->ticket->status->label()}.",
            'url'          => route('tickets.show', $this->ticket),
        ];
    }
}