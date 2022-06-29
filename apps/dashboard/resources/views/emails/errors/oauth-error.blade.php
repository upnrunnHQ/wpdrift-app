@component('mail::message')
# Hello Admin,

Error occurred upon store authorization process.

For site to {{config('app.name')}}.

@component('mail::panel')
  Store Name : {{ $store->name }}
  <br />
  Store ID : {{ $store->id }}
  <br />
  URL: {{ $store->auth_server_url }}
  <br />
  Store User: {{ $user->name }}
  <br />
  Error Message: {{ $error }}
  <br />
  Call Response: <pre>{!! print_r($call_response, true) !!}</pre>
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
