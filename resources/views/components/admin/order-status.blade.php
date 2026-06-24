@props(['status', 'type' => 'order'])
@php
    $class = match($status) {
        'paid', 'completed' => 'bg-emerald-50 text-emerald-700',
        'cancelled', 'failed', 'refunded' => 'bg-rose-50 text-rose-700',
        'confirmed', 'processing', 'shipped' => 'bg-indigo-50 text-indigo-700',
        default => 'bg-amber-50 text-amber-700',
    };
@endphp
@php($prefix = match($type) { 'payment' => 'payment_', 'fulfillment' => 'fulfillment_', default => 'status_' })
<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $class }}">{{ __('admin.orders.'.$prefix.$status) }}</span>
