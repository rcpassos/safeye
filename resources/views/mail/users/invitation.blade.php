<x-mail::message>
# Invitation to join a Team

You have been invited to join the team {{ $teamInvitation->team->name }}. Please click on the button below to accept the invitation.

<x-mail::button :url="$team_url">
    Accept Invitation
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
