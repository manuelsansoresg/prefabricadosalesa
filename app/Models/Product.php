<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'title',
        'unit',
        'description',
        'tech_specs',
        'image_path',
        'datasheet_path',
    ];

    protected $casts = [
        'tech_specs' => 'array',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }
}
