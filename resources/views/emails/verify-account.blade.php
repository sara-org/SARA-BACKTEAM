@component('mail::message')
<h2> Hi , We are S.A.R.A , we have received your request to verify your account ! </h2>
<p> Please , you can use the following code to verify your account : </p>

@component('mail::panel')
{{ $code }}
@endcomponent

<p>The allowed duration of the code is one hour from the time the message was sent</p>
@endcomponent