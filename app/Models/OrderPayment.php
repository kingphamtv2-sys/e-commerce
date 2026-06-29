<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderPayment extends Model
{
    protected $fillable = [
        'order_id',
        'payment_method_code',
        'payment_method_name',
        'payment_status',
        'amount',
        'currency_code',
        'transaction_id',
        'instruction',
        'selected_at',
        'paid_at',
        'snapshot',
        'gateway_response',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'selected_at' => 'datetime',
            'paid_at' => 'datetime',
            'snapshot' => 'array',
            'gateway_response' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }
}
