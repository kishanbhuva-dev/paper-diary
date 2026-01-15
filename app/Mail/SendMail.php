<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @var array<string, mixed>
     */
    public array $data;

    /**
     * Create a new message instance.
     *
     * @param mixed                $data
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Password Notification',
        );
    }

    /**
     * Get the message content definition.
     */
    // public function content(): Content
    // {
    //     return new Content(
    //         view: 'view.name',
    //     );
    // }
    public function build(): self
    {
        return $this->view('emails.reset-password')
            ->with($this->data);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
