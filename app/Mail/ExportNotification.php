<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExportNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $format, // 'pdf' or 'csv'
        public string $content, // File content
        public string $filename,
        public array $stats, // Stats to display
        public ?User $sentByAdmin = null, // Admin who sent the export
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.from.address'),
            to: $this->user->email,
            subject: "📥 Votre export de watchlist KDrama est prêt!",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.export-notification',
            with: [
                'user' => $this->user,
                'format' => $this->format,
                'filename' => $this->filename,
                'stats' => $this->stats,
                'sentByAdmin' => $this->sentByAdmin,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn() => $this->content,
                $this->filename
            )->withMime($this->format === 'pdf' ? 'application/pdf' : 'text/csv'),
        ];
    }
}
