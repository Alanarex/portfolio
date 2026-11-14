<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contribution_calendars', function (Blueprint $table): void {
            $table->id();
            $table->string('source', 20);
            $table->string('username', 100);
            // Map of ISO date => contribution count (counts only, never repository names).
            $table->json('days');
            $table->timestamp('synced_at');
            $table->timestamps();
            $table->unique(['source', 'username']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contribution_calendars');
    }
};
