<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutContent extends Model
{
    protected $fillable = [
        'body',
        'headline',
        'image_path',
        'mission',
        'history',
        'card_1_title',
        'card_1_body',
        'card_2_title',
        'card_2_body',
    ];
}
