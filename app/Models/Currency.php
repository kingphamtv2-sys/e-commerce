<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'exchange_rate',
        'decimal_places',
        'symbol_position',
        'thousand_separator',
        'decimal_separator',
        'is_default',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'exchange_rate' => 'decimal:6',
            'decimal_places' => 'integer',
            'is_default' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }
}
