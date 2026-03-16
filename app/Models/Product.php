<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image_path',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }
}
