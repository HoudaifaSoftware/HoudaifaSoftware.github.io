@component('mail::message')
# Votre cabinet est activé

Bonjour {{ $ownerName }},

Bonne nouvelle : votre cabinet **{{ $cabinetName }}** a été activé. Vous pouvez
désormais vous connecter et commencer à utiliser l'application.

@component('mail::button', ['url' => $loginUrl])
Se connecter
@endcomponent

Merci de votre confiance,<br>
L'équipe {{ config('app.name') }}
@endcomponent
