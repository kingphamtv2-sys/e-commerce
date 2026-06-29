<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingMethod extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const TYPE_FLAT_RATE = 'flat_rate';

    public const TYPE_FREE_SHIPPING = 'free_shipping';

    public const TYPE_PICKUP = 'pickup';

    protected $fillable = [
        'shipping_zone_id',
        'code',
        'name',
        'description',
        'type',
        'base_fee',
        'free_shipping_min_amount',
        'min_order_amount',
        'max_order_amount',
        'estimated_delivery_min_days',
        'estimated_delivery_max_days',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'base_fee' => 'decimal:2',
            'free_shipping_min_amount' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_order_amount' => 'decimal:2',
            'estimated_delivery_min_days' => 'integer',
            'estimated_delivery_max_days' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
