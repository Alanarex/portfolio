<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('github_activity_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->string('username', 100)->unique();
            $table->json('repositories');
            $table->json('events');
            $table->timestamp('synced_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('github_activity_snapshots');
    }
};
