<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Career\Database\Seeders\CareerDatabaseSeeder;
use Modules\Projects\Database\Seeders\ProjectsDatabaseSeeder;
use Modules\Settings\Database\Seeders\SettingsDatabaseSeeder;
use Modules\Skills\Database\Seeders\SkillsDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SettingsDatabaseSeeder::class);
        $this->call(CareerDatabaseSeeder::class);
        $this->call(SkillsDatabaseSeeder::class);
        $this->call(ProjectsDatabaseSeeder::class);
    }
}
