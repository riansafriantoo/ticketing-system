<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SlaBreaching extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Ticket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isOverdue = $this->ticket->isOverdue();
        $label     = $isOverdue ? '🚨 SLA BREACHED' : '⚠️ SLA WARNING';

        return (new MailMessage)
            ->subject("[{$this->ticket->ticketNumber()}] {$label}: {$this->ticket->subject}")
            ->greeting("Hello {$notifiable->name},")
            ->line($isOverdue
                ? "**SLA has been breached** for the following ticket."
                : "This ticket is approaching its SLA deadline.")
            ->line("**{$this->ticket->ticketNumber()}** — {$this->ticket->subject}")
            ->line("Priority: **{$this->ticket->priority->label()}** | SLA due: {$this->ticket->sla_due_at?->format('M d, Y H:i')}")
            ->action('View Ticket Now', route('tickets.show', $this->ticket))
            ->line('Please take immediate action.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'ticket_id'    => $this->ticket->id,
            'ticket_number'=> $this->ticket->ticketNumber(),
            'subject'      => $this->ticket->subject,
            'message'      => "SLA alert for ticket {$this->ticket->ticketNumber()}.",
            'url'          => route('tickets.show', $this->ticket),
        ];
    }
}