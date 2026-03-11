<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Setting;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $subject_text;
    public $message_text;

    /**
     * Create a new message instance.
     */
    public function __construct(array $data)
    {
        $this->name = $data['name'];
        $this->email = $data['email'];
        $this->subject_text = $data['subject'];
        $this->message_text = $data['message'];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Get site name from settings, fallback to APP_NAME
        $fromName = Setting::get('site_name') ?? env('APP_NAME', 'KDrama Hub');
        $fromAddress = env('MAIL_FROM_ADDRESS') ?? 'admin@moussouni.dev';

        return new Envelope(
            subject: 'Nouveau message de contact : ' . $this->subject_text,
            from: new Address($fromAddress, $fromName),
            replyTo: [$this->email],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contact',
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
