<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:20px 0;border-collapse:collapse">
    <tr>
        <td style="padding:8px 0;color:#64748b">{{ __('emails.order_number') }}</td>
        <td align="right" style="padding:8px 0;font-weight:700">{{ $order->order_code }}</td>
    </tr>
    <tr>
        <td style="padding:8px 0;color:#64748b">{{ __('emails.order_date') }}</td>
        <td align="right" style="padding:8px 0">{{ ($order->placed_at ?? $order->created_at)?->format('Y-m-d H:i') }}</td>
    </tr>
    <tr>
        <td style="padding:8px 0;color:#64748b">{{ __('emails.payment_method') }}</td>
        <td align="right" style="padding:8px 0">{{ $order->payment_method_name ?: strtoupper($order->payment_method) }}</td>
    </tr>
    <tr>
        <td style="padding:8px 0;color:#64748b">{{ __('emails.payment_status') }}</td>
        <td align="right" style="padding:8px 0">{{ __('emails.statuses.'.$order->payment_status) }}</td>
    </tr>
    <tr>
        <td style="padding:8px 0;color:#64748b">{{ __('emails.order_status') }}</td>
        <td align="right" style="padding:8px 0">{{ __('emails.statuses.'.$order->order_status) }}</td>
    </tr>
</table>

<h2 style="font-size:16px;margin:24px 0 10px">{{ __('emails.items') }}</h2>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;font-size:13px">
    <thead>
        <tr style="background:#f8fafc">
            <th align="left" style="padding:10px;border-bottom:1px solid #e2e8f0">{{ __('emails.item') }}</th>
            <th align="center" style="padding:10px;border-bottom:1px solid #e2e8f0">{{ __('emails.quantity') }}</th>
            <th align="right" style="padding:10px;border-bottom:1px solid #e2e8f0">{{ __('emails.price') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td style="padding:10px;border-bottom:1px solid #e2e8f0">
                    <strong>{{ $item->product_name }}</strong>
                    @if($item->variant_name)<br><span style="color:#64748b">{{ $item->variant_name }}</span>@endif
                    <br><span style="color:#94a3b8">{{ $item->sku ?: $item->product_sku }}</span>
                </td>
                <td align="center" style="padding:10px;border-bottom:1px solid #e2e8f0">{{ $item->quantity }}</td>
                <td align="right" style="padding:10px;border-bottom:1px solid #e2e8f0">{{ $money($item->total) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:16px;border-collapse:collapse">
    <tr><td style="padding:5px 0;color:#64748b">{{ __('emails.subtotal') }}</td><td align="right">{{ $money($order->subtotal) }}</td></tr>
    @if((float)$order->discount_amount > 0)<tr><td style="padding:5px 0;color:#64748b">{{ __('emails.discount') }}</td><td align="right">-{{ $money($order->discount_amount) }}</td></tr>@endif
    @if((float)$order->tax_amount > 0)<tr><td style="padding:5px 0;color:#64748b">{{ __('emails.tax') }}</td><td align="right">{{ $money($order->tax_amount) }}</td></tr>@endif
    <tr><td style="padding:5px 0;color:#64748b">{{ __('emails.shipping') }}</td><td align="right">{{ $money($order->shipping_fee) }}</td></tr>
    <tr><td style="padding:12px 0 5px;font-weight:700;border-top:1px solid #cbd5e1">{{ __('emails.total') }}</td><td align="right" style="padding-top:12px;font-size:17px;font-weight:700;border-top:1px solid #cbd5e1">{{ $money($order->total_amount) }}</td></tr>
</table>

@if($shipping)
    <h2 style="font-size:16px;margin:24px 0 8px">{{ __('emails.shipping_address') }}</h2>
    <p style="margin:0;line-height:1.6;color:#475569">
        {{ $shipping->full_name }} · {{ $shipping->phone }}<br>
        {{ collect([$shipping->address_line, $shipping->ward, $shipping->district, $shipping->province, $shipping->country_code])->filter()->implode(', ') }}
    </p>
@endif
