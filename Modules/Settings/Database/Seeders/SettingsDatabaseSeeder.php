<?php

declare(strict_types=1);

namespace Modules\Settings\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class SettingsDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['code' => 'fr', 'label' => 'Français', 'direction' => 'ltr', 'status' => 'active'],
            ['code' => 'en', 'label' => 'English', 'direction' => 'ltr', 'status' => 'active'],
            ['code' => 'ar', 'label' => 'العربية', 'direction' => 'rtl', 'status' => 'readiness'],
        ] as $locale) {
            DB::table('locales')->updateOrInsert(
                ['code' => $locale['code']],
                [...$locale, 'updated_at' => now(), 'created_at' => now()],
            );
        }
    }
}
