<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'checkout_session_id',
        'order_code',
        'success_token',
        'customer_name',
        'customer_phone',
        'customer_email',
        'language_code',
        'contact_snapshot',
        'shipping_address',
        'shipping_address_snapshot',
        'billing_address_snapshot',
        'currency_code',
        'currency_symbol',
        'currency_symbol_position',
        'currency_decimal_places',
        'currency_snapshot',
        'exchange_rate',
        'subtotal',
        'discount_amount',
        'coupon_snapshot',
        'shipping_method_id',
        'shipping_method_code',
        'shipping_method_name',
        'shipping_method_description',
        'shipping_zone_id',
        'shipping_zone_name',
        'base_shipping_amount',
        'tax_amount',
        'tax_snapshot',
        'shipping_fee',
        'shipping_estimated_delivery',
        'total_amount',
        'payment_method',
        'payment_method_name',
        'payment_status',
        'payment_instruction',
        'order_status',
        'fulfillment_status',
        'note',
        'admin_note',
        'confirmed_at',
        'completed_at',
        'cancelled_at',
        'inventory_restocked_at',
        'placed_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'contact_snapshot' => 'array',
            'shipping_address_snapshot' => 'array',
            'billing_address_snapshot' => 'array',
            'currency_snapshot' => 'array',
            'coupon_snapshot' => 'array',
            'base_shipping_amount' => 'decimal:2',
            'tax_snapshot' => 'array',
            'currency_decimal_places' => 'integer',
            'exchange_rate' => 'decimal:6',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'confirmed_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'inventory_restocked_at' => 'datetime',
            'placed_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function checkoutSession(): BelongsTo
    {
        return $this->belongsTo(CheckoutSession::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function orderPayments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function orderAddresses(): HasMany
    {
        return $this->hasMany(OrderAddress::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function internalNotes(): HasMany
    {
        return $this->hasMany(OrderNote::class);
    }

    public function paymentHistories(): HasMany
    {
        return $this->hasMany(OrderPaymentHistory::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function shippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class);
    }
}
