<?php

namespace App\Mail;

use App\Models\CustomerInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CustomerInvite $invite,
        public string $registrationUrl
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You\'re Invited to Access Your Customer Portal',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer-invitation',
            with: [
                'invite' => $this->invite,
                'registrationUrl' => $this->registrationUrl,
                'companyName' => $this->invite->company->name,
                'customerName' => $this->invite->customer?->full_name ?? 'Valued Customer',
                'expiresAt' => $this->invite->expires_at,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
