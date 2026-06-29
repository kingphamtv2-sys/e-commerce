<?php

namespace App\Mail;

use App\Models\EmailLog;
use App\Services\OrderEmailDataService;
use App\Services\SystemSettingService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransactionalMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly EmailLog $emailLog) {}

    public function build(): self
    {
        $settings = app(SystemSettingService::class);
        $view = 'emails.'.str_replace('_', '-', $this->emailLog->event);
        $data = [
            'emailLog' => $this->emailLog,
            'storeName' => $settings->get('site_name', config('app.name')),
            'supportEmail' => $settings->get('site_email'),
            'payload' => $this->emailLog->payload ?? [],
        ];

        if ($this->emailLog->order) {
            $data = array_merge($data, app(OrderEmailDataService::class)->data($this->emailLog->order));
        }

        $mail = $this
            ->subject($this->emailLog->subject)
            ->view($view)
            ->with($data);

        $fromAddress = $settings->get('email_from_address');
        $fromName = $settings->get('email_from_name');
        if (filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
            $mail->from($fromAddress, $fromName ?: $data['storeName']);
        }

        return $mail;
    }
}
