<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_setting_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_setting_id')->constrained('site_settings')->cascadeOnDelete();
            $table->string('email');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('site_setting_phones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_setting_id')->constrained('site_settings')->cascadeOnDelete();
            $table->string('phone');
            $table->string('whatsapp_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        if (Schema::hasColumn('site_settings', 'contact_emails')) {
            $settings = DB::table('site_settings')->get(['id', 'contact_emails']);

            foreach ($settings as $row) {
                $raw = (string) ($row->contact_emails ?? '');
                $lines = preg_split('/\r\n|\r|\n/', $raw, -1, PREG_SPLIT_NO_EMPTY);

                $i = 0;
                foreach ($lines as $line) {
                    $email = trim($line);
                    if ($email === '') {
                        continue;
                    }

                    DB::table('site_setting_emails')->insert([
                        'site_setting_id' => $row->id,
                        'email' => $email,
                        'sort_order' => $i,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $i++;
                }
            }
        }

        if (Schema::hasColumn('site_settings', 'contact_phones')) {
            $settings = DB::table('site_settings')->get(['id', 'contact_phones']);

            foreach ($settings as $row) {
                $raw = (string) ($row->contact_phones ?? '');
                $lines = preg_split('/\r\n|\r|\n/', $raw, -1, PREG_SPLIT_NO_EMPTY);

                $i = 0;
                foreach ($lines as $line) {
                    $phone = trim($line);
                    if ($phone === '') {
                        continue;
                    }

                    $digits = preg_replace('/\D+/', '', $phone);
                    $whatsappUrl = $digits ? 'https://wa.me/'.$digits : null;

                    DB::table('site_setting_phones')->insert([
                        'site_setting_id' => $row->id,
                        'phone' => $phone,
                        'whatsapp_url' => $whatsappUrl,
                        'sort_order' => $i,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $i++;
                }
            }
        }

        $hasEmails = Schema::hasColumn('site_settings', 'contact_emails');
        $hasPhones = Schema::hasColumn('site_settings', 'contact_phones');

        Schema::table('site_settings', function (Blueprint $table) use ($hasEmails, $hasPhones) {
            if ($hasEmails) {
                $table->dropColumn('contact_emails');
            }
            if ($hasPhones) {
                $table->dropColumn('contact_phones');
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->text('contact_emails')->nullable();
            $table->text('contact_phones')->nullable();
        });

        Schema::dropIfExists('site_setting_phones');
        Schema::dropIfExists('site_setting_emails');
    }
};
