<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table): void {
            $table->string('portrait_path')->nullable();
            $table->string('portrait_checksum', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table): void {
            $table->dropColumn(['portrait_path', 'portrait_checksum']);
        });
    }
};
