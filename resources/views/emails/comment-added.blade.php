@component('mail::message')
# {{ 'New reply' }}

Hi,

{{ $comment->user->name }} {{ 'replied to' }} ticket **{{ $ticket->ticketNumber() }}**.

@component('mail::panel')
{{ $comment->body }}
@endcomponent

@component('mail::button', ['url' => route('tickets.show', $ticket)])
View Ticket
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent