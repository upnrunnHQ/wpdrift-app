@component('mail::message')
# Hello,

New site has been added to {{config('app.name')}} by {{$user->name}}.

@component('mail::panel')
  Site Name: {{ $store->name }}
  <br />
  URL: {{ $store->auth_server_url }}
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
