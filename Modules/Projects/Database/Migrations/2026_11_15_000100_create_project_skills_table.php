<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_skills', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            // Stable key owned by the Skills module; no cross-module foreign key.
            $table->string('skill_key', 80);
            $table->timestamps();
            $table->unique(['project_id', 'skill_key']);
            $table->index('skill_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_skills');
    }
};
