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
        'tax_amount',
        'tax_snapshot',
        'shipping_fee',
        'total_amount',
        'payment_method',
        'payment_method_name',
        'payment_status',
        'payment_instruction',
        'order_status',
        'note',
        'admin_note',
        'confirmed_at',
        'completed_at',
        'cancelled_at',
        'placed_at',
    ];

    protected function casts(): array
    {
        return [
            'contact_snapshot' => 'array',
            'shipping_address_snapshot' => 'array',
            'billing_address_snapshot' => 'array',
            'currency_snapshot' => 'array',
            'coupon_snapshot' => 'array',
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
            'placed_at' => 'datetime',
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
}
