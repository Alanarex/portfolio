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
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_administrator')->default(false)->index();
            $table->unsignedBigInteger('auth_version')->default(1);
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE UNIQUE INDEX users_single_administrator ON users (is_administrator) WHERE is_administrator = true');
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS users_single_administrator');
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['is_administrator']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['is_administrator', 'auth_version']);
        });
    }
};
