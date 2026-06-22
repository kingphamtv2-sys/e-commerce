<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantImage extends Model
{
    protected $fillable = ['product_variant_id', 'image_path', 'alt_text', 'is_main', 'sort_order', 'status'];

    protected function casts(): array
    {
        return ['is_main' => 'boolean', 'sort_order' => 'integer', 'status' => 'boolean'];
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
