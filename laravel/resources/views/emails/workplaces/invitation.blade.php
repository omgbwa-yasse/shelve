@component('mail::message')
# Invitation à rejoindre un espace de travail

Bonjour,

{{ $invitation->inviter->name }} vous a invité à rejoindre l'espace de travail **{{ $invitation->workplace->name }}** en tant que **{{ $invitation->proposed_role }}**.

@if($invitation->message)
**Message:**
{{ $invitation->message }}
@endif

@component('mail::button', ['url' => route('workplaces.invitations.accept', $invitation->token)])
Accepter l'invitation
@endcomponent

Ce lien expirera le {{ $invitation->expires_at->format('d/m/Y à H:i') }}.

Merci,<br>
{{ config('app.name') }}
@endcomponent
