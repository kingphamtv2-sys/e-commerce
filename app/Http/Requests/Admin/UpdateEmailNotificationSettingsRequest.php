<?php

namespace App\Http\Requests\Admin;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailNotificationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email_notifications_enabled' => ['required', 'boolean'],
            'admin_order_email_enabled' => ['required', 'boolean'],
            'customer_order_email_enabled' => ['required', 'boolean'],
            'payment_email_enabled' => ['required', 'boolean'],
            'payment_failed_email_enabled' => ['required', 'boolean'],
            'order_status_email_enabled' => ['required', 'boolean'],
            'admin_notification_emails' => [
                'nullable',
                'string',
                'max:2000',
                function (string $attribute, mixed $value, Closure $fail): void {
                    foreach (array_filter(array_map('trim', explode(',', (string) $value))) as $email) {
                        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $fail(__('admin.email_settings.invalid_admin_emails'));

                            return;
                        }
                    }
                },
            ],
            'email_from_name' => ['nullable', 'string', 'max:255'],
            'email_from_address' => ['nullable', 'email', 'max:320'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email_notifications_enabled' => $this->boolean('email_notifications_enabled'),
            'admin_order_email_enabled' => $this->boolean('admin_order_email_enabled'),
            'customer_order_email_enabled' => $this->boolean('customer_order_email_enabled'),
            'payment_email_enabled' => $this->boolean('payment_email_enabled'),
            'payment_failed_email_enabled' => $this->boolean('payment_failed_email_enabled'),
            'order_status_email_enabled' => $this->boolean('order_status_email_enabled'),
        ]);
    }
}
