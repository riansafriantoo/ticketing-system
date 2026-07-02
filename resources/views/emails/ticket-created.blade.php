@component('mail::message')
# New ticket created

Hi {{ $ticket->requester->name }},

Your ticket **{{ $ticket->ticketNumber() }}** has been created and is now in the queue.

@component('mail::panel')
**{{ $ticket->subject }}**

{{ \Illuminate\Support\Str::limit($ticket->description, 200) }}
@endcomponent

| | |
|---|---|
| **Priority** | {{ $ticket->priority->label() }} |
| **Category** | {{ $ticket->category->label() }} |
| **SLA due by** | {{ $ticket->sla_due_at?->format('M d, Y H:i') ?? '—' }} |

@component('mail::button', ['url' => route('tickets.show', $ticket)])
View Ticket
@endcomponent

We'll keep you posted as it's worked on.

Thanks,<br>
{{ config('app.name') }}
@endcomponent