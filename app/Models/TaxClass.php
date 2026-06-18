<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxClass extends Model
{
    protected $fillable = ['name', 'code', 'description', 'status'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function taxRates(): HasMany
    {
        return $this->hasMany(TaxRate::class);
    }
}
