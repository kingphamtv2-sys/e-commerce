<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'image_path', 'alt_text', 'sort_order', 'is_main'];

    protected function casts(): array
    {
        return ['is_main' => 'boolean'];
    }
}
