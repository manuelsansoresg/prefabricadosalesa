<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->text('contact_form_to_emails')->nullable()->after('map_embed_url');
            $table->text('contact_form_bcc_emails')->nullable()->after('contact_form_to_emails');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'contact_form_to_emails',
                'contact_form_bcc_emails',
            ]);
        });
    }
};
