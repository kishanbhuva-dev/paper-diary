<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Booking extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct($data,string $recipientType)
    {
        $this->data = $data;
        $this->recipientType = $recipientType; // 
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = ($this->recipientType === 'owner')
            ? 'New Booking Received'
            : 'Your Booking is Confirmed';
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        
        $view = ($this->recipientType === 'owner')
            ? 'emails.owner_booking'
            : 'emails.user_booking';

        return new Content(
            view: $view,
            with: ['data' => $this->data],
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
