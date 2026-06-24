<div id="order-status-summary" class="flex flex-wrap items-center gap-3">
    <x-admin.order-status :status="$order->order_status" />
    <x-admin.order-status :status="$order->payment_status" type="payment" />
    <x-admin.order-status :status="$order->fulfillment_status" type="fulfillment" />
    @if($order->inventory_restocked_at)
        <span class="rounded-full bg-sky-50 px-2.5 py-1 text-xs font-bold text-sky-700">{{ __('admin.orders.restocked') }}</span>
    @endif
</div>
