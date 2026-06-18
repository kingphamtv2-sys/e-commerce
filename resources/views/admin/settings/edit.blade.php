@extends('layouts.admin')

@section('title', __('admin.settings.title'))

@section('content')
    @php
        $inputClass = 'mt-2 block w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
        $labelClass = 'text-sm font-semibold text-slate-800';
        $helpClass = 'mt-1 text-xs leading-5 text-slate-500';
    @endphp

    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">
            <span class="grid h-7 w-7 place-items-center rounded-full bg-emerald-100 text-emerald-700">✓</span>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
            <p class="font-bold">{{ __('admin.settings.validation_title') }}</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5">
                <h2 class="text-base font-bold text-slate-950">{{ __('admin.settings.general') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('admin.settings.general_desc') }}</p>
            </div>
            <div class="grid gap-6 p-6 md:grid-cols-2">
                <div>
                    <label for="site_name" class="{{ $labelClass }}">{{ __('admin.settings.site_name') }} <span class="text-rose-500">*</span></label>
                    <input id="site_name" name="site_name" type="text" value="{{ old('site_name', $settings['site_name']) }}" class="{{ $inputClass }}" required>
                    @error('site_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="site_email" class="{{ $labelClass }}">{{ __('admin.settings.site_email') }}</label>
                    <input id="site_email" name="site_email" type="email" value="{{ old('site_email', $settings['site_email']) }}" class="{{ $inputClass }}" placeholder="support@example.com">
                    @error('site_email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="site_phone" class="{{ $labelClass }}">{{ __('admin.settings.site_phone') }}</label>
                    <input id="site_phone" name="site_phone" type="text" value="{{ old('site_phone', $settings['site_phone']) }}" class="{{ $inputClass }}" placeholder="+84 123 456 789">
                    @error('site_phone') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="site_address" class="{{ $labelClass }}">{{ __('admin.settings.site_address') }}</label>
                    <textarea id="site_address" name="site_address" rows="3" class="{{ $inputClass }}">{{ old('site_address', $settings['site_address']) }}</textarea>
                    @error('site_address') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="site_logo" class="{{ $labelClass }}">{{ __('admin.settings.logo_path') }}</label>
                    <input id="site_logo" name="site_logo" type="text" value="{{ old('site_logo', $settings['site_logo']) }}" class="{{ $inputClass }}" placeholder="/storage/branding/logo.svg">
                    <p class="{{ $helpClass }}">Enter a public path or URL. File upload is intentionally outside this task.</p>
                    @error('site_logo') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="site_favicon" class="{{ $labelClass }}">{{ __('admin.settings.favicon_path') }}</label>
                    <input id="site_favicon" name="site_favicon" type="text" value="{{ old('site_favicon', $settings['site_favicon']) }}" class="{{ $inputClass }}" placeholder="/storage/branding/favicon.ico">
                    <p class="{{ $helpClass }}">Recommended: a square SVG, PNG or ICO public path.</p>
                    @error('site_favicon') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5">
                <h2 class="text-base font-bold text-slate-950">{{ __('admin.settings.localization') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('admin.settings.localization_desc') }}</p>
            </div>
            <div class="grid gap-6 p-6 md:grid-cols-2">
                <div>
                    <label for="default_language" class="{{ $labelClass }}">{{ __('admin.settings.default_language') }} <span class="text-rose-500">*</span></label>
                    <select id="default_language" name="default_language" class="{{ $inputClass }}" required>
                        @foreach (['vi' => 'Vietnamese (vi)', 'en' => 'English (en)', 'ja' => 'Japanese (ja)'] as $code => $label)
                            <option value="{{ $code }}" @selected(old('default_language', $settings['default_language']) === $code)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('default_language') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="default_currency" class="{{ $labelClass }}">{{ __('admin.settings.default_currency') }} <span class="text-rose-500">*</span></label>
                    <select id="default_currency" name="default_currency" class="{{ $inputClass }}" required>
                        @foreach (['VND' => 'Vietnamese Dong (VND)', 'USD' => 'US Dollar (USD)', 'JPY' => 'Japanese Yen (JPY)'] as $code => $label)
                            <option value="{{ $code }}" @selected(old('default_currency', $settings['default_currency']) === $code)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('default_currency') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                @foreach ([
                    'multi_language_enabled' => [__('admin.settings.multi_language'), 'Allow customers to switch between supported languages.'],
                    'multi_currency_enabled' => [__('admin.settings.multi_currency'), 'Allow customers to view prices in supported currencies.'],
                ] as $key => [$label, $description])
                    <label class="flex cursor-pointer items-start justify-between gap-4 rounded-xl border border-slate-200 p-4 transition hover:border-indigo-200 hover:bg-indigo-50/30">
                        <span>
                            <span class="block text-sm font-semibold text-slate-800">{{ $label }}</span>
                            <span class="mt-1 block text-xs leading-5 text-slate-500">{{ $description }}</span>
                        </span>
                        <input name="{{ $key }}" type="checkbox" value="1" class="mt-1 h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(old($key, $settings[$key]))>
                    </label>
                @endforeach
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5">
                <h2 class="text-base font-bold text-slate-950">{{ __('admin.settings.tax') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('admin.settings.tax_desc') }}</p>
            </div>
            <div class="grid gap-4 p-6 md:grid-cols-2">
                @foreach ([
                    'tax_enabled' => [__('admin.settings.enable_tax'), 'Calculate tax for taxable products during checkout.'],
                    'price_include_tax' => [__('admin.settings.prices_include_tax'), 'Treat catalog prices as already inclusive of tax.'],
                ] as $key => [$label, $description])
                    <label class="flex cursor-pointer items-start justify-between gap-4 rounded-xl border border-slate-200 p-4 transition hover:border-indigo-200 hover:bg-indigo-50/30">
                        <span>
                            <span class="block text-sm font-semibold text-slate-800">{{ $label }}</span>
                            <span class="mt-1 block text-xs leading-5 text-slate-500">{{ $description }}</span>
                        </span>
                        <input name="{{ $key }}" type="checkbox" value="1" class="mt-1 h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(old($key, $settings[$key]))>
                    </label>
                @endforeach
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5">
                <h2 class="text-base font-bold text-slate-950">{{ __('admin.settings.order') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('admin.settings.order_desc') }}</p>
            </div>
            <div class="grid gap-6 p-6 md:grid-cols-3">
                <div>
                    <label for="default_shipping_fee" class="{{ $labelClass }}">{{ __('admin.settings.shipping_fee') }} <span class="text-rose-500">*</span></label>
                    <input id="default_shipping_fee" name="default_shipping_fee" type="number" min="0" step="1" value="{{ old('default_shipping_fee', $settings['default_shipping_fee']) }}" class="{{ $inputClass }}" required>
                    @error('default_shipping_fee') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="free_shipping_min_amount" class="{{ $labelClass }}">{{ __('admin.settings.free_shipping_min') }}</label>
                    <input id="free_shipping_min_amount" name="free_shipping_min_amount" type="number" min="0" step="1" value="{{ old('free_shipping_min_amount', $settings['free_shipping_min_amount']) }}" class="{{ $inputClass }}">
                    @error('free_shipping_min_amount') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="order_code_prefix" class="{{ $labelClass }}">{{ __('admin.settings.order_prefix') }} <span class="text-rose-500">*</span></label>
                    <input id="order_code_prefix" name="order_code_prefix" type="text" value="{{ old('order_code_prefix', $settings['order_code_prefix']) }}" class="{{ $inputClass }}" maxlength="20" required>
                    <p class="{{ $helpClass }}">Letters, numbers, hyphens and underscores only.</p>
                    @error('order_code_prefix') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <div class="sticky bottom-4 flex justify-end rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-lg backdrop-blur">
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                {{ __('admin.settings.save') }}
            </button>
        </div>
    </form>
@endsection
