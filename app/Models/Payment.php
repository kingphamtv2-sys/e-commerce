<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['order_id', 'payment_method', 'transaction_id', 'amount', 'currency_code', 'status', 'paid_at', 'raw_response'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'paid_at' => 'datetime', 'raw_response' => 'array'];
    }
}
