@component('mail::message')
# Status updated

Hi,

The status of ticket **{{ $ticket->ticketNumber() }}** has changed.

@component('mail::panel')
**{{ $ticket->subject }}**

{{ $oldStatus->label() }} → **{{ $newStatus->label() }}**
@endcomponent

@if($newStatus->value === 'resolved')
Your issue has been marked as resolved. If everything looks good, no further action is needed — the ticket will close automatically. If you're still experiencing the issue, please reply to let us know.
@elseif($newStatus->value === 'on_hold')
This ticket has been placed on hold. We'll resume work and notify you as soon as it's back in progress.
@elseif($newStatus->value === 'closed')
This ticket has been closed. If you need to reopen it, just reply and we'll take another look.
@endif

@component('mail::button', ['url' => route('tickets.show', $ticket)])
View Ticket
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent