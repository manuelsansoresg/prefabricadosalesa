<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gallery_images', function (Blueprint $table) {
            $table->string('media_type')->default('image')->after('id');
            $table->string('video_path')->nullable()->after('thumb_path');
            $table->string('video_cover_path')->nullable()->after('video_path');
            $table->string('video_cover_thumb_path')->nullable()->after('video_cover_path');
        });
    }

    public function down(): void
    {
        Schema::table('gallery_images', function (Blueprint $table) {
            $table->dropColumn([
                'media_type',
                'video_path',
                'video_cover_path',
                'video_cover_thumb_path',
            ]);
        });
    }
};
