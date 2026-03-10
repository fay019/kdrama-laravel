<?php

namespace App\Jobs;

use App\Mail\ExportNotification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendExportEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public string $format,
        public string $content,
        public string $filename,
        public array $stats,
        public ?User $sentByAdmin = null,
    ) {
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Décoder le contenu depuis base64
            $decodedContent = base64_decode($this->content, true);

            if ($decodedContent === false) {
                throw new \Exception('Failed to decode base64 content');
            }

            Mail::send(new ExportNotification(
                user: $this->user,
                format: $this->format,
                content: $decodedContent,
                filename: $this->filename,
                stats: $this->stats,
                sentByAdmin: $this->sentByAdmin,
            ));

            \Log::info('Export email sent successfully', [
                'user_id' => $this->user->id,
                'format' => $this->format,
                'filename' => $this->filename,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send export email', [
                'user_id' => $this->user->id,
                'format' => $this->format,
                'error' => $e->getMessage(),
            ]);

            // Re-throw pour que Laravel retry le job
            throw $e;
        }
    }
}
