<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CustomerExportReady extends Mailable
{
    use Queueable, SerializesModels;

    public $filename;

    public $customerCount;

    /**
     * Create a new message instance.
     */
    public function __construct(string $filename, int $customerCount)
    {
        $this->filename = $filename;
        $this->customerCount = $customerCount;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Customer Export is Ready',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.customer-export-ready',
            with: [
                'filename' => $this->filename,
                'customerCount' => $this->customerCount,
                'downloadUrl' => url('/download/customer-export/'.encrypt($this->filename)),
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
        $path = Storage::disk('local')->path('exports/'.$this->filename);

        return [
            Attachment::fromPath($path)
                ->as($this->filename)
                ->withMime('text/csv'),
        ];
    }
}
