<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">@vite(['resources/css/app.css'])<title>{{ __('storefront.payment_result_title') }}</title></head>
<body class="min-h-screen bg-slate-50 p-4 text-slate-900">
<main class="mx-auto mt-10 max-w-2xl rounded-[2rem] border border-slate-200 bg-white p-7 shadow-sm sm:p-10">
    @php($paid = $order->payment_status === 'paid')
    <div @class(['mx-auto grid h-16 w-16 place-items-center rounded-full text-2xl font-black','bg-emerald-100 text-emerald-700' => $paid,'bg-amber-100 text-amber-800' => $order->payment_status === 'pending','bg-rose-100 text-rose-700' => ! $paid && $order->payment_status !== 'pending'])>{{ $paid ? '✓' : '!' }}</div>
    <h1 class="mt-5 text-center text-3xl font-black">{{ __("storefront.payment_result_{$order->payment_status}") }}</h1>
    <p class="mt-2 text-center text-sm text-slate-500">{{ __('storefront.payment_result_help') }}</p>
    @if($errors->any())<p class="mt-5 rounded-xl bg-rose-50 p-4 text-sm font-bold text-rose-700">{{ $errors->first() }}</p>@endif
    <dl class="mt-7 space-y-3 rounded-2xl bg-slate-50 p-5 text-sm"><div class="flex justify-between"><dt>{{ __('storefront.order_number') }}</dt><dd class="font-black">{{ $order->order_code }}</dd></div><div class="flex justify-between"><dt>{{ __('storefront.grand_total') }}</dt><dd class="font-black">{{ number_format((float)$order->total_amount, $order->currency_decimal_places ?? 0) }} {{ $order->currency_code }}</dd></div><div class="flex justify-between gap-3"><dt>{{ __('storefront.payment_transaction') }}</dt><dd class="break-all text-right font-bold">{{ $transaction?->transaction_number ?: '—' }}</dd></div></dl>
    <div class="mt-7 grid gap-3 sm:grid-cols-2">@if(in_array($order->payment_status, ['failed','cancelled','expired'], true))<form method="POST" action="{{ route('orders.payment.retry', $order) }}">@csrf<button class="w-full rounded-xl bg-indigo-600 px-5 py-3 text-sm font-black text-white">{{ __('storefront.payment_retry') }}</button></form>@endif<a href="{{ route('orders.success', $order->success_token) }}" class="rounded-xl border border-slate-300 px-5 py-3 text-center text-sm font-black text-slate-700">{{ __('storefront.view_order') }}</a><a href="{{ route('products.index') }}" class="rounded-xl border border-slate-300 px-5 py-3 text-center text-sm font-black text-slate-700">{{ __('storefront.continue_shopping') }}</a></div>
</main>
</body></html>
