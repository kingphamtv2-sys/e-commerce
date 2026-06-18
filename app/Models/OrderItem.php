<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'product_variant_id', 'product_name', 'product_sku', 'variant_name', 'price', 'quantity', 'subtotal', 'tax_rate', 'tax_amount', 'total'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'subtotal' => 'decimal:2', 'tax_rate' => 'decimal:4', 'tax_amount' => 'decimal:2', 'total' => 'decimal:2'];
    }
}
