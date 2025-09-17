<?php

namespace App\Mail;

use App\Models\TeamInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public TeamInvite $invite,
        public string $registrationUrl
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $inviterName = $this->invite->invitedBy->name;
        $companyName = $this->invite->company?->name ?? 'Service Core';

        return new Envelope(
            subject: "You're invited to join {$companyName} team",
            replyTo: [$this->invite->invitedBy->email => $inviterName],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.team-invitation',
            with: [
                'inviterName' => $this->invite->invitedBy->name,
                'inviteeName' => $this->invite->name,
                'companyName' => $this->invite->company?->name ?? 'Service Core',
                'role' => $this->invite->getRoleDescription(),
                'message' => $this->invite->message,
                'registrationUrl' => $this->registrationUrl,
                'expiresAt' => $this->invite->expires_at,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
