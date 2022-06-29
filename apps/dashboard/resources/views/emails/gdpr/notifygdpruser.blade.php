@component('mail::message')
# Hello {{$user->name}},

You just downloaded GDRP Data from {{config('app.name')}}.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
