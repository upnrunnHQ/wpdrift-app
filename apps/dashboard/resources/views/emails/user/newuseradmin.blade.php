@component('mail::message')
# Hello,

There is new user signup with {{ config('app.name') }}, below are the details.

@component('mail::panel')
  Name: {{ $user->name }}
  <br />
  Email: {{ $user->email }}
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
