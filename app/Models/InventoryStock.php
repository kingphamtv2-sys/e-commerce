<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryStock extends Model
{
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'quantity',
        'reserved_quantity',
        'low_stock_threshold',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function inventoryLogs(): HasMany
    {
        return $this->hasMany(InventoryLog::class);
    }

    public function availableQuantity(): int
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    public function stockStatus(): string
    {
        $available = $this->availableQuantity();

        return match (true) {
            $available === 0 => 'out_of_stock',
            $available <= $this->low_stock_threshold => 'low_stock',
            default => 'in_stock',
        };
    }
}
