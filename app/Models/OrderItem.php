<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'product_sku',
        'variant_name',
        'sku',
        'option_values_snapshot',
        'image',
        'price',
        'quantity',
        'subtotal',
        'tax_name',
        'taxable_amount',
        'tax_rate',
        'tax_amount',
        'total',
        'product_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'option_values_snapshot' => 'array',
            'product_snapshot' => 'array',
            'price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'taxable_amount' => 'decimal:2',
            'tax_rate' => 'decimal:4',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }
}
