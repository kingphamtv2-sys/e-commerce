<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    public const ONLINE_CODE = 'online';

    protected $fillable = [
        'code', 'name', 'description', 'instruction', 'gateway_code', 'environment',
        'config', 'credentials', 'min_order_amount', 'max_order_amount', 'sort_order', 'status',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'credentials' => 'encrypted:array',
            'min_order_amount' => 'decimal:2',
            'max_order_amount' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function isUsable(): bool
    {
        return $this->status === 'active'
            && filled($this->gateway_code)
            && filled($this->credentials['secret_key'] ?? null);
    }
}
