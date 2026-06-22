<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductOptionValue extends Model
{
    protected $fillable = ['product_option_id', 'value', 'display_value', 'color_code', 'sort_order', 'status'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'status' => 'boolean'];
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class, 'product_option_id');
    }

    public function variantOptionValues(): HasMany
    {
        return $this->hasMany(ProductVariantOptionValue::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function label(): string
    {
        return $this->display_value ?: $this->value;
    }
}
