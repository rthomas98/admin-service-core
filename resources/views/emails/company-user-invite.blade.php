<x-mail::message>
# You're Invited to Join {{ $companyName }}

Hello{{ $invite->name ? ' ' . $invite->name : '' }},

{{ $inviterName }} has invited you to join **{{ $companyName }}** as a **{{ $role }}**.

<x-mail::button :url="$inviteUrl">
Accept Invitation
</x-mail::button>

This invitation will expire on **{{ $expiresAt }}**.

If you have any questions, please contact your administrator.

Thanks,<br>
{{ config('app.name') }}

<x-mail::subcopy>
If you're having trouble clicking the "Accept Invitation" button, copy and paste the URL below into your web browser: {{ $inviteUrl }}
</x-mail::subcopy>
</x-mail::message>