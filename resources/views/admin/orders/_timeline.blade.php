@php
    $events = $order->statusHistories->map(fn($history) => [
        'type' => 'status', 'date' => $history->created_at, 'actor' => $history->changedBy?->name ?? __('admin.orders.system'),
        'title' => __('admin.orders.status_changed', ['from' => $history->from_status ? __('admin.orders.status_'.$history->from_status) : '—', 'to' => __('admin.orders.status_'.$history->to_status)]),
        'body' => $history->note,
    ])->concat($order->paymentHistories->map(fn($history) => [
        'type' => 'payment', 'date' => $history->created_at, 'actor' => $history->changedBy?->name ?? __('admin.orders.system'),
        'title' => __('admin.orders.payment_changed', ['from' => __('admin.orders.payment_'.$history->from_status), 'to' => __('admin.orders.payment_'.$history->to_status)]),
        'body' => $history->note,
    ]))->concat($order->internalNotes->map(fn($note) => [
        'type' => $note->type, 'date' => $note->created_at, 'actor' => $note->createdBy?->name ?? __('admin.orders.system'),
        'title' => $note->type === 'internal' ? __('admin.orders.internal_note') : __('admin.orders.system_note'), 'body' => $note->note,
    ]))->sortByDesc('date')->values();
@endphp
<div id="order-timeline" class="space-y-5">
    @forelse($events as $event)
        <article class="relative pl-8">
            <span @class(['absolute left-0 top-1 grid h-5 w-5 place-items-center rounded-full', 'bg-indigo-100 text-indigo-700' => $event['type'] === 'status', 'bg-emerald-100 text-emerald-700' => $event['type'] === 'payment', 'bg-amber-100 text-amber-700' => !in_array($event['type'], ['status','payment'], true)])><span class="h-2 w-2 rounded-full bg-current"></span></span>
            <p class="text-sm font-bold text-slate-900">{{ $event['title'] }}</p>
            @if($event['body'])<p class="mt-1 whitespace-pre-line text-sm text-slate-600">{{ $event['body'] }}</p>@endif
            <p class="mt-1 text-xs text-slate-400">{{ $event['actor'] }} · {{ $event['date']?->format('Y-m-d H:i') }}</p>
        </article>
    @empty
        <p class="text-sm text-slate-500">{{ __('admin.orders.timeline_empty') }}</p>
    @endforelse
</div>
