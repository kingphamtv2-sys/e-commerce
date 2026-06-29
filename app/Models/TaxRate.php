<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxRate extends Model
{
    protected $fillable = [
        'tax_class_id',
        'country_code',
        'region',
        'rate',
        'priority',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:4',
            'priority' => 'integer',
            'status' => 'boolean',
        ];
    }

    public function taxClass(): BelongsTo
    {
        return $this->belongsTo(TaxClass::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
