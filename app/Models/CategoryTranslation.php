<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryTranslation extends Model
{
    protected $fillable = ['category_id', 'language_code', 'name', 'slug', 'description', 'meta_title', 'meta_description'];
}
