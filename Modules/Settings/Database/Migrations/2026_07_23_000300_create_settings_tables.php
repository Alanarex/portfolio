<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locales', function (Blueprint $table): void {
            $table->string('code', 2)->primary();
            $table->string('label', 80);
            $table->string('direction', 3)->default('ltr');
            $table->string('status', 20)->default('readiness')->index();
            $table->timestamps();
        });

        $now = now();
        DB::table('locales')->insert([
            ['code' => 'fr', 'label' => 'Français', 'direction' => 'ltr', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'en', 'label' => 'English', 'direction' => 'ltr', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'ar', 'label' => 'العربية', 'direction' => 'rtl', 'status' => 'readiness', 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::create('site_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 50)->unique();
            $table->string('site_name', 120);
            $table->string('default_locale', 2)->default('fr');
            $table->text('contact_email')->nullable();
            $table->text('contact_phone')->nullable();
            $table->boolean('show_email')->default(false);
            $table->boolean('show_phone')->default(false);
            $table->timestamps();
        });

        Schema::create('social_links', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_setting_id')->constrained('site_settings')->cascadeOnDelete();
            $table->string('platform', 40);
            $table->text('url');
            $table->boolean('is_enabled')->default(false);
            $table->boolean('is_public')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['site_setting_id', 'platform']);
        });

        Schema::create('public_feature_flags', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_setting_id')->constrained('site_settings')->cascadeOnDelete();
            $table->string('key', 50);
            $table->boolean('enabled')->default(false);
            $table->timestamps();
            $table->unique(['site_setting_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_feature_flags');
        Schema::dropIfExists('social_links');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('locales');
    }
};
