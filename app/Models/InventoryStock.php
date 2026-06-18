<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryStock extends Model
{
    protected $fillable = ['product_id', 'product_variant_id', 'quantity', 'low_stock_threshold'];
}
