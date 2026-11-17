<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nature de l'événement : failed, locked, reset_requested, reset_completed (null = connexion réussie).
        Schema::table('admin_login_events', function (Blueprint $table): void {
            $table->string('reason', 32)->nullable()->after('succeeded');
        });
    }

    public function down(): void
    {
        Schema::table('admin_login_events', function (Blueprint $table): void {
            $table->dropColumn('reason');
        });
    }
};
