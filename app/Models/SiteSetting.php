<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteSetting extends Model
{
    protected $fillable = [
        'hero_video_path',
        'whatsapp_number',
        'whatsapp_message',
    ];

    public function contactEmails(): HasMany
    {
        return $this->hasMany(SiteSettingEmail::class)->orderBy('sort_order')->orderBy('id');
    }

    public function contactPhones(): HasMany
    {
        return $this->hasMany(SiteSettingPhone::class)->orderBy('sort_order')->orderBy('id');
    }
}
