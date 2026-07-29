<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cv_versions', function (Blueprint $table): void {
            $table->string('disk', 50)->nullable()->after('version_label');
            $table->string('path')->nullable()->unique()->after('disk');
        });
    }

    public function down(): void
    {
        Schema::table('cv_versions', function (Blueprint $table): void {
            $table->dropUnique(['path']);
        });
        Schema::table('cv_versions', function (Blueprint $table): void {
            $table->dropColumn(['disk', 'path']);
        });
    }
};
