<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteSetting extends Model
{
    protected $fillable = [
        'hero_video_path',
        'contact_address',
        'map_embed_url',
        'contact_form_to_emails',
        'contact_form_bcc_emails',
        'whatsapp_number',
        'whatsapp_message',
        'whatsapp_floating_enabled',
    ];

    protected $casts = [
        'whatsapp_floating_enabled' => 'bool',
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
