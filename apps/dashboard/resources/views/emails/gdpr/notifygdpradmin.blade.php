@component('mail::message')
# Hello,

User downloaded GDRP DATA from {{config('app.name')}} by {{$user->name}}.

@component('mail::panel')
  User Name: {{ $user->name }}
  <br />
  Email: {{ $user->email }}
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
