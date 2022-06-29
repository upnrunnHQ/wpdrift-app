@component('mail::message')
# Hello,

EDD Section has been setup to {{config('app.name')}} by {{$user->name}}.

@component('mail::panel')
  To enable EDD section and dashboard, you can enable it from
  <a href="{{ config('app.url') }}/settings/sites/{{ $store->id }}">Site Setting page</a>.
  <br />
  Site Name: {{ $store->name }}
  <br />
  URL: {{ $store->auth_server_url }}
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
