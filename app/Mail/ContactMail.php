<?php

namespace App\Mail;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;

    public $email;

    public $subject_text;

    public $message_text;

    public $drama_image;

    public $page_url;

    public $ip_address;

    /**
     * Create a new message instance.
     */
    public function __construct(array $data)
    {
        $this->name = $data['name'];
        $this->email = $data['email'];
        $this->subject_text = $data['subject'];
        $this->message_text = $data['message'];
        $this->drama_image = $data['drama_image'] ?? null;
        $this->page_url = $data['page_url'] ?? null;
        $this->ip_address = $data['ip_address'] ?? null;
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
            subject: 'Nouveau message de contact : '.$this->subject_text,
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
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
