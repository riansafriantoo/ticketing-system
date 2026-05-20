<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreated extends Notification implements ShouldQueue
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
            ->subject("[{$this->ticket->ticketNumber()}] Ticket Created: {$this->ticket->subject}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your support ticket has been created successfully.")
            ->line("**{$this->ticket->ticketNumber()}** — {$this->ticket->subject}")
            ->line("Priority: **{$this->ticket->priority->label()}** | SLA due by: {$this->ticket->sla_due_at?->format('M d, Y H:i')}")
            ->action('View Ticket', route('tickets.show', $this->ticket))
            ->line('Our team will respond within the SLA window. Thank you for reaching out.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'ticket_id'    => $this->ticket->id,
            'ticket_number'=> $this->ticket->ticketNumber(),
            'subject'      => $this->ticket->subject,
            'message'      => "New ticket {$this->ticket->ticketNumber()} created.",
            'url'          => route('tickets.show', $this->ticket),
        ];
    }
}