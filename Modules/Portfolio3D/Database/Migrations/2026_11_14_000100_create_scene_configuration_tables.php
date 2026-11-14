<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scene_configurations', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 50)->unique();
            $table->boolean('enabled')->default(false);
            $table->string('asset_version', 40)->default('placeholder-1');
            $table->string('poster_path', 255)->nullable();
            $table->string('mobile_fallback_path', 255)->nullable();
            $table->string('greeting_fr', 280)->nullable();
            $table->string('greeting_en', 280)->nullable();
            $table->string('performance_mode', 20)->default('auto');
            $table->string('reduced_motion_mode', 20)->default('respect');
            $table->timestamps();
        });

        Schema::create('scene_hotspots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('scene_configuration_id')->constrained('scene_configurations')->cascadeOnDelete();
            $table->string('key', 40);
            $table->string('target', 40);
            $table->boolean('is_visible')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('label_fr', 80)->nullable();
            $table->string('label_en', 80)->nullable();
            $table->string('description_fr', 280)->nullable();
            $table->string('description_en', 280)->nullable();
            $table->timestamps();
            $table->unique(['scene_configuration_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scene_hotspots');
        Schema::dropIfExists('scene_configurations');
    }
};
