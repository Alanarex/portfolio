<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** La section « En dehors du code » est masquée par défaut ; elle se réactive dans Paramètres > Sections. */
return new class extends Migration
{
    public function up(): void
    {
        $settingsId = DB::table('site_settings')->where('key', 'main')->value('id');
        if ($settingsId === null) {
            return;
        }

        $exists = DB::table('public_feature_flags')
            ->where('site_setting_id', $settingsId)
            ->where('key', 'section_interets')
            ->exists();
        if (! $exists) {
            DB::table('public_feature_flags')->insert([
                'site_setting_id' => $settingsId,
                'key' => 'section_interets',
                'enabled' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('public_feature_flags')->where('key', 'section_interets')->delete();
    }
};
