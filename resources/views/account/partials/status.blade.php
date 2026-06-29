@php
    $colors = match($status) {
        'paid', 'completed', 'delivered' => 'bg-emerald-50 text-emerald-700',
        'failed', 'cancelled', 'refunded' => 'bg-rose-50 text-rose-700',
        'confirmed', 'processing', 'shipped' => 'bg-indigo-50 text-indigo-700',
        default => 'bg-amber-50 text-amber-700',
    };
@endphp
<span class="inline-flex rounded-full px-3 py-1 text-xs font-extrabold {{ $colors }}">
    {{ __('account.statuses.'.$status) }}
</span>
