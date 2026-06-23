<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPayment extends Model
{
    protected $fillable = [
        'order_id',
        'payment_method_code',
        'payment_method_name',
        'payment_status',
        'amount',
        'currency_code',
        'instruction',
        'selected_at',
        'paid_at',
        'snapshot',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'selected_at' => 'datetime',
            'paid_at' => 'datetime',
            'snapshot' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
