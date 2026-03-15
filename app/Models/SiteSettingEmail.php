<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteSettingEmail extends Model
{
    protected $table = 'site_setting_emails';

    protected $fillable = [
        'site_setting_id',
        'email',
        'sort_order',
    ];

    public function siteSetting(): BelongsTo
    {
        return $this->belongsTo(SiteSetting::class);
    }
}
