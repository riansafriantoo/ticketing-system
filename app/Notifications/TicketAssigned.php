<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Ticket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[{$this->ticket->ticketNumber()}] Assigned to You: {$this->ticket->subject}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A ticket has been assigned to you.")
            ->line("**{$this->ticket->ticketNumber()}** — {$this->ticket->subject}")
            ->line("Priority: **{$this->ticket->priority->label()}** | SLA due: {$this->ticket->sla_due_at?->format('M d, Y H:i')}")
            ->action('View & Respond', route('tickets.show', $this->ticket));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'ticket_id'    => $this->ticket->id,
            'ticket_number'=> $this->ticket->ticketNumber(),
            'subject'      => $this->ticket->subject,
            'message'      => "Ticket {$this->ticket->ticketNumber()} assigned to you.",
            'url'          => route('tickets.show', $this->ticket),
        ];
    }
}