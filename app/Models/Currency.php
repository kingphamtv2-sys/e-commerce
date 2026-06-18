<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = ['code', 'name', 'symbol', 'exchange_rate', 'decimal_places', 'symbol_position', 'thousand_separator', 'decimal_separator', 'is_default', 'status'];

    protected function casts(): array
    {
        return ['exchange_rate' => 'decimal:6', 'is_default' => 'boolean', 'status' => 'boolean'];
    }
}
