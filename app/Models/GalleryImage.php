<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryImage extends Model
{
    protected $fillable = [
        'media_type',
        'image_path',
        'thumb_path',
        'video_path',
        'video_cover_path',
        'video_cover_thumb_path',
    ];
}
