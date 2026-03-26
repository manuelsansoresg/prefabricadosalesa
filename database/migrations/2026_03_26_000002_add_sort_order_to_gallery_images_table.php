<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gallery_images', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('media_type')->index();
        });

        $maxId = (int) (DB::table('gallery_images')->max('id') ?? 0);
        if ($maxId > 0) {
            DB::table('gallery_images')->update([
                'sort_order' => DB::raw($maxId.' - id'),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('gallery_images', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
