<x-mail::message>
# Welcome to the {{ $companyName }} Team!

Hi {{ $inviteeName ?? 'there' }},

{{ $inviterName }} has invited you to join the {{ $companyName }} team as a **{{ $role }}**.

@if($message)
## Personal Message
{{ $message }}
@endif

## Your Role: {{ $role }}

As a {{ $role }}, you'll have access to the administrative panel where you can manage operations, view reports, and collaborate with the team.

<x-mail::button :url="$registrationUrl">
Accept Invitation & Set Up Your Account
</x-mail::button>

This invitation will expire on **{{ $expiresAt->format('F j, Y \a\t g:i A') }}**.

If you have any questions, please don't hesitate to reach out to {{ $inviterName }} or our support team.

## Need Help?

If you're having trouble clicking the button above, copy and paste the URL below into your web browser:

<x-mail::panel>
{{ $registrationUrl }}
</x-mail::panel>

Thanks,<br>
The {{ $companyName }} Team
</x-mail::message>