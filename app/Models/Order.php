<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = ['user_id', 'order_code', 'customer_name', 'customer_phone', 'customer_email', 'shipping_address', 'currency_code', 'exchange_rate', 'subtotal', 'discount_amount', 'tax_amount', 'shipping_fee', 'total_amount', 'payment_method', 'payment_status', 'order_status', 'note', 'admin_note', 'confirmed_at', 'completed_at', 'cancelled_at'];

    protected function casts(): array
    {
        return ['exchange_rate' => 'decimal:6', 'subtotal' => 'decimal:2', 'discount_amount' => 'decimal:2', 'tax_amount' => 'decimal:2', 'shipping_fee' => 'decimal:2', 'total_amount' => 'decimal:2', 'confirmed_at' => 'datetime', 'completed_at' => 'datetime', 'cancelled_at' => 'datetime'];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
