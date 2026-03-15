<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteSettingPhone extends Model
{
    protected $table = 'site_setting_phones';

    protected $fillable = [
        'site_setting_id',
        'phone',
        'whatsapp_url',
        'sort_order',
    ];

    public function siteSetting(): BelongsTo
    {
        return $this->belongsTo(SiteSetting::class);
    }
}
