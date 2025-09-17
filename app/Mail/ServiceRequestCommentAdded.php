<?php

namespace App\Mail;

use App\Models\ServiceRequest;
use App\Models\ServiceRequestActivity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceRequestCommentAdded extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public ServiceRequest $serviceRequest,
        public ServiceRequestActivity $activity
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Comment on Service Request - '.$this->serviceRequest->title,
            to: [$this->serviceRequest->customer->email],
            replyTo: [config('mail.from.address')],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.service-request.comment-added',
            with: [
                'serviceRequest' => $this->serviceRequest,
                'activity' => $this->activity,
                'customer' => $this->serviceRequest->customer,
                'company' => $this->serviceRequest->company,
                'commenter' => $this->activity->user,
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
