<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('about_contents', function (Blueprint $table) {
            $table->string('headline')->nullable()->after('body');
            $table->string('image_path')->nullable()->after('headline');
            $table->longText('mission')->nullable()->after('image_path');
            $table->longText('history')->nullable()->after('mission');

            $table->string('card_1_title')->nullable()->after('history');
            $table->longText('card_1_body')->nullable()->after('card_1_title');
            $table->string('card_2_title')->nullable()->after('card_1_body');
            $table->longText('card_2_body')->nullable()->after('card_2_title');
        });
    }

    public function down(): void
    {
        Schema::table('about_contents', function (Blueprint $table) {
            $table->dropColumn([
                'headline',
                'image_path',
                'mission',
                'history',
                'card_1_title',
                'card_1_body',
                'card_2_title',
                'card_2_body',
            ]);
        });
    }
};
