<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAddress extends Model
{
    protected $fillable = [
        'order_id',
        'type',
        'full_name',
        'phone',
        'country_code',
        'province',
        'district',
        'ward',
        'address_line',
        'raw_snapshot',
    ];

    protected function casts(): array
    {
        return ['raw_snapshot' => 'array'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
