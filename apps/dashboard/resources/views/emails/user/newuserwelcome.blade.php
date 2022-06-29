@component('mail::message')
# Welcome {{$user->name}}

Thanks for registering with our app. Thanks **appreciated**, keep _connected_ and reffer you friends.

@component('mail::panel')
	The email address you signed up with is: {{$user->email}}
@endcomponent

@component('mail::button', ['url' => config('app.url')])
View My Dashboard
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
