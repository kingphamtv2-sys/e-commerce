<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingZone extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'code',
        'description',
        'countries',
        'cities',
        'districts',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'countries' => 'array',
            'cities' => 'array',
            'districts' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function methods(): HasMany
    {
        return $this->hasMany(ShippingMethod::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
