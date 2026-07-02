@component('mail::message')
# Ticket assigned

Hi,

@if($newAssignee)
Ticket **{{ $ticket->ticketNumber() }}** has been assigned to **{{ $newAssignee->name }}**.
@else
Ticket **{{ $ticket->ticketNumber() }}** has been unassigned.
@endif

@component('mail::panel')
**{{ $ticket->subject }}**

{{ \Illuminate\Support\Str::limit($ticket->description, 200) }}
@endcomponent

| | |
|---|---|
| **Priority** | {{ $ticket->priority->label() }} |
| **Requester** | {{ $ticket->requester->name }} |
| **SLA due by** | {{ $ticket->sla_due_at?->format('M d, Y H:i') ?? '—' }} |

@component('mail::button', ['url' => route('tickets.show', $ticket)])
View Ticket
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent