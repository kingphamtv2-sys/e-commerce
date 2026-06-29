<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingAddress extends Model
{
    protected $fillable = ['user_id', 'full_name', 'phone', 'country_code', 'province', 'district', 'ward', 'address_line', 'is_default'];

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }
}
