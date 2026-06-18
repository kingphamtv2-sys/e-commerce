<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = ['user_id', 'session_id', 'currency_code'];

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
