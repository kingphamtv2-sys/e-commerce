<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['product_id', 'product_variant_id', 'type', 'quantity', 'before_quantity', 'after_quantity', 'note', 'created_by'];
}
