<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserWelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $temporaryPassword,
        public ?string $personalMessage = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to '.config('app.name').' - Your Account Details',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user-welcome',
            with: [
                'userName' => $this->user->name,
                'userEmail' => $this->user->email,
                'temporaryPassword' => $this->temporaryPassword,
                'loginUrl' => url('/admin/login'),
                'appName' => config('app.name'),
                'personalMessage' => $this->personalMessage,
                'userRoles' => $this->user->roles->pluck('name')->implode(', '),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
