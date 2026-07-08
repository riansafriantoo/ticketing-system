@component('mail::message')
# Ticket Assigned

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
| **Priority**:  | {{ $ticket->priority instanceof \App\Enums\TicketPriority ? $ticket->priority->label() : ucfirst($ticket->priority) }} |
| **Requester**:  | {{ $ticket->requester?->name ?? '—' }} |
| **Assigned to**:  | {{ $newAssignee?->name ?? 'Unassigned' }} |

@component('mail::button', ['url' => route('tickets.show', $ticket)])
View Ticket
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent