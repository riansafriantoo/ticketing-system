@component('mail::message')
# New ticket created

@php
    $priorityLabel = $ticket->priority instanceof \App\Enums\TicketPriority ? $ticket->priority->label() : ucfirst($ticket->priority);

    $caseTypeLabel = $ticket->case_type instanceof \App\Enums\TicketCaseType ? $ticket->case_type->label() : ucfirst($ticket->case_type);
@endphp

Hi {{ $ticket->requester->name }},

Your ticket **{{ $ticket->ticketNumber() }}** has been created and is now in the queue.

@component('mail::panel')
**{{ $ticket->subject }}**

{{ \Illuminate\Support\Str::limit($ticket->description, 200) }}
@endcomponent

| | |
|---|---|
| **Priority: ** | {{ $priorityLabel }} |
| **Case Type: ** | {{ $caseTypeLabel }} |

@component('mail::button', ['url' => route('tickets.show', $ticket)])
View Ticket
@endcomponent

We'll keep you posted as it's worked on.

Thanks,<br>
{{ config('app.name') }}
@endcomponent