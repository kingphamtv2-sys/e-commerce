<?php

namespace App\Jobs;

use App\Mail\TransactionalMail;
use App\Models\EmailLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendEmailNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(public readonly int $emailLogId)
    {
        $this->onQueue('emails')->afterCommit();
    }

    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(): void
    {
        $log = EmailLog::query()->with('order')->find($this->emailLogId);
        if (! $log || $log->status === 'sent') {
            return;
        }

        $log->forceFill([
            'status' => 'pending',
            'attempts' => $log->attempts + 1,
            'error_message' => null,
            'failed_at' => null,
        ])->save();

        try {
            Mail::to($log->recipient_email)
                ->locale($log->locale)
                ->send(new TransactionalMail($log));

            $log->forceFill([
                'status' => 'sent',
                'sent_at' => now(),
                'failed_at' => null,
                'error_message' => null,
            ])->save();
        } catch (Throwable $exception) {
            $this->recordFailure($log, $exception);
            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        $log = EmailLog::query()->find($this->emailLogId);
        if ($log && $log->status !== 'sent') {
            $this->recordFailure($log, $exception);
        }
    }

    private function recordFailure(EmailLog $log, ?Throwable $exception): void
    {
        $log->forceFill([
            'status' => 'failed',
            'failed_at' => now(),
            'error_message' => $exception ? class_basename($exception) : 'MailDeliveryFailed',
        ])->save();
    }
}
