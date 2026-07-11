<?php

namespace App\Listeners\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Mailable;

trait ReliableMailSender
{
    /**
     * Send a Mailable to each recipient individually, with per-send
     * retry logic and fresh SMTP connections.
     *
     * @param Collection   $recipients  Collection of User models
     * @param Mailable     $mailable    The Mailable instance to send
     * @param string       $eventType   For logging: 'TicketCreated', 'CommentAdded', etc.
     * @param array        $logContext  Additional context for error logs
     * @param int          $maxRetries  Retry attempts per recipient (default: 3)
     * @param int          $retryDelay  Milliseconds between retries (default: 2000)
     * @param int          $sendDelay   Milliseconds between each recipient (default: 500)
     */
    protected function sendToEach(
        Collection $recipients,
        Mailable   $mailable,
        string     $eventType,
        array      $logContext = [],
        int        $maxRetries = 3,
        int        $retryDelay = 2000,
        int        $sendDelay  = 500,
    ): void {
        foreach ($recipients as $index => $user) {

            if ($index > 0 && $sendDelay > 0) {
                usleep($sendDelay * 1000);
            }

            try {
                retry($maxRetries, function () use ($user, $mailable) {

                    $this->resetSmtpConnection();

                    Mail::to($user)->send(clone $mailable);
                }, $retryDelay);

            } catch (\Throwable $e) {

                Log::error("{$eventType} email failed", array_merge($logContext, [
                    'recipient' => $user->email,
                    'error'     => $e->getMessage(),
                ]));
            }
        }
    }

    private function resetSmtpConnection(): void
    {
        try {
            app('mail.manager')->purge('smtp');
        } catch (\Throwable $e) {
            // Silently ignore — if we can't purge, the next send()
            // will either reuse the existing connection (which might
            // work fine) or fail and get caught by the retry logic.
        }
    }
}