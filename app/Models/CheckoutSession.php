<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CheckoutSession extends Model
{
    protected $fillable = [
        'cart_id',
        'user_id',
        'session_id',
        'token',
        'status',
        'contact_name',
        'contact_email',
        'contact_phone',
        'shipping_address',
        'billing_address',
        'billing_same_as_shipping',
        'items_snapshot',
        'tax_snapshot',
        'currency_snapshot',
        'coupon_snapshot',
        'shipping_method_id',
        'shipping_method_code',
        'shipping_method_name',
        'shipping_method_description',
        'shipping_zone_id',
        'shipping_zone_name',
        'base_shipping_amount',
        'shipping_estimated_delivery',
        'payment_method_code',
        'payment_method_name',
        'payment_status',
        'payment_amount',
        'payment_currency_code',
        'payment_instruction',
        'payment_selected_at',
        'note',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'shipping_amount',
        'grand_total',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
            'billing_address' => 'array',
            'billing_same_as_shipping' => 'boolean',
            'items_snapshot' => 'array',
            'tax_snapshot' => 'array',
            'currency_snapshot' => 'array',
            'coupon_snapshot' => 'array',
            'base_shipping_amount' => 'decimal:2',
            'payment_amount' => 'decimal:2',
            'payment_selected_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'expires_at' => 'datetime',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
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
