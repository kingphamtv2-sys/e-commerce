@extends('layouts.admin')

@section('title', __('admin.email_settings.title'))

@section('content')
    @php
        $inputClass = 'mt-2 block w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
    @endphp

    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
            <p class="font-bold">{{ __('admin.email_settings.validation_title') }}</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <form method="POST" action="{{ route('admin.settings.email.update') }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5">
                    <h2 class="font-extrabold">{{ __('admin.email_settings.notifications') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('admin.email_settings.notifications_help') }}</p>
                </div>
                <div class="grid gap-4 p-6 md:grid-cols-2">
                    @foreach ([
                        'email_notifications_enabled' => ['master_enabled', 'master_enabled_help'],
                        'customer_order_email_enabled' => ['customer_order', 'customer_order_help'],
                        'admin_order_email_enabled' => ['admin_order', 'admin_order_help'],
                        'payment_email_enabled' => ['payment', 'payment_help'],
                        'payment_failed_email_enabled' => ['payment_failed', 'payment_failed_help'],
                        'order_status_email_enabled' => ['order_status', 'order_status_help'],
                    ] as $key => [$label, $help])
                        <label class="flex cursor-pointer items-start justify-between gap-4 rounded-xl border border-slate-200 p-4 hover:bg-slate-50">
                            <span>
                                <span class="block text-sm font-bold text-slate-900">{{ __('admin.email_settings.'.$label) }}</span>
                                <span class="mt-1 block text-xs leading-5 text-slate-500">{{ __('admin.email_settings.'.$help) }}</span>
                            </span>
                            <input type="checkbox" name="{{ $key }}" value="1" class="mt-1 h-5 w-5 rounded border-slate-300 text-indigo-600" @checked(old($key, $settings[$key]))>
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5">
                    <h2 class="font-extrabold">{{ __('admin.email_settings.recipients_sender') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('admin.email_settings.recipients_sender_help') }}</p>
                </div>
                <div class="grid gap-6 p-6 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="admin_notification_emails" class="text-sm font-bold">{{ __('admin.email_settings.admin_emails') }}</label>
                        <textarea id="admin_notification_emails" name="admin_notification_emails" rows="3" class="{{ $inputClass }}" placeholder="orders@example.com, owner@example.com">{{ old('admin_notification_emails', $settings['admin_notification_emails']) }}</textarea>
                        <p class="mt-1 text-xs text-slate-500">{{ __('admin.email_settings.admin_emails_help') }}</p>
                    </div>
                    <div>
                        <label for="email_from_name" class="text-sm font-bold">{{ __('admin.email_settings.from_name') }}</label>
                        <input id="email_from_name" name="email_from_name" value="{{ old('email_from_name', $settings['email_from_name']) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label for="email_from_address" class="text-sm font-bold">{{ __('admin.email_settings.from_address') }}</label>
                        <input id="email_from_address" name="email_from_address" type="email" value="{{ old('email_from_address', $settings['email_from_address']) }}" class="{{ $inputClass }}">
                    </div>
                    <p class="md:col-span-2 text-xs leading-5 text-slate-500">{{ __('admin.email_settings.from_help') }}</p>
                </div>
            </section>

            <div class="flex justify-end">
                <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">{{ __('admin.email_settings.save') }}</button>
            </div>
        </form>

        <aside class="space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="font-extrabold">{{ __('admin.email_settings.runtime') }}</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div><dt class="text-slate-400">{{ __('admin.email_settings.mailer') }}</dt><dd class="font-bold">{{ $mailConfig['mailer'] }}</dd></div>
                    <div><dt class="text-slate-400">{{ __('admin.email_settings.queue') }}</dt><dd class="font-bold">{{ $mailConfig['queue'] }}</dd></div>
                    <div><dt class="text-slate-400">{{ __('admin.email_settings.env_from') }}</dt><dd class="break-all font-semibold">{{ $mailConfig['from_name'] }} &lt;{{ $mailConfig['from_address'] }}&gt;</dd></div>
                </dl>
                <p class="mt-4 rounded-xl bg-amber-50 p-3 text-xs leading-5 text-amber-800">{{ __('admin.email_settings.secret_notice') }}</p>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="font-extrabold">{{ __('admin.email_settings.test_title') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('admin.email_settings.test_help') }}</p>
                <form method="POST" action="{{ route('admin.settings.email.test') }}" class="mt-4">
                    @csrf
                    <label for="recipient_email" class="text-sm font-bold">{{ __('admin.email_settings.test_recipient') }}</label>
                    <input id="recipient_email" name="recipient_email" type="email" required value="{{ old('recipient_email', auth()->user()->email) }}" class="{{ $inputClass }}">
                    <button class="mt-4 w-full rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white">{{ __('admin.email_settings.send_test') }}</button>
                </form>
            </section>
        </aside>
    </div>
@endsection

