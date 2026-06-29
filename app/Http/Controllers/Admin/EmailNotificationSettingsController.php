<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendTestEmailRequest;
use App\Http\Requests\Admin\UpdateEmailNotificationSettingsRequest;
use App\Services\EmailNotificationService;
use App\Services\SystemSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmailNotificationSettingsController extends Controller
{
    public function edit(SystemSettingService $settings): View
    {
        $keys = array_keys(array_filter(
            SystemSettingService::DEFINITIONS,
            fn (array $definition): bool => $definition['group'] === 'email',
        ));
        $defaults = collect($keys)->mapWithKeys(
            fn (string $key): array => [$key => SystemSettingService::DEFINITIONS[$key]['default']],
        )->all();

        return view('admin.settings.email', [
            'settings' => array_replace($defaults, Arr::only($settings->all(), $keys)),
            'mailConfig' => [
                'mailer' => config('mail.default'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
                'queue' => config('queue.default'),
            ],
        ]);
    }

    public function update(
        UpdateEmailNotificationSettingsRequest $request,
        SystemSettingService $settings,
    ): RedirectResponse {
        $values = $request->validated();
        $values['admin_notification_emails'] = collect(explode(',', (string) ($values['admin_notification_emails'] ?? '')))
            ->map(fn (string $email): string => strtolower(trim($email)))
            ->filter()
            ->unique()
            ->implode(',');

        DB::transaction(function () use ($values, $settings): void {
            foreach ($values as $key => $value) {
                $definition = SystemSettingService::DEFINITIONS[$key];
                $settings->set($key, $value, $definition['type'], $definition['group'], false);
            }
        });

        return back()->with('success', __('admin.email_settings.updated'));
    }

    public function test(
        SendTestEmailRequest $request,
        EmailNotificationService $emails,
    ): RedirectResponse {
        $log = $emails->testEmail($request->validated('recipient_email'));

        return back()->with(
            $log->status === 'failed' ? 'error' : 'success',
            $log->status === 'failed'
                ? __('admin.email_settings.test_failed')
                : __('admin.email_settings.test_queued'),
        );
    }
}
