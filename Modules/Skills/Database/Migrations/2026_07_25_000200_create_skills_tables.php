<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skill_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('status', 20)->default('draft');
            $table->boolean('is_visible')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'is_visible', 'sort_order']);
        });

        Schema::create('skill_category_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('skill_category_id')->constrained('skill_categories')->cascadeOnDelete();
            $table->string('locale', 2);
            $table->string('name', 120);
            $table->timestamps();
            $table->unique(['skill_category_id', 'locale'], 'skill_category_locale_unique');
        });

        Schema::create('skills', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('skill_category_id')->constrained('skill_categories')->restrictOnDelete();
            $table->string('key', 100)->unique();
            $table->string('status', 20)->default('draft');
            $table->boolean('is_visible')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['skill_category_id', 'status', 'is_visible', 'sort_order'], 'skills_public_idx');
        });

        Schema::create('skill_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('skill_id')->constrained('skills')->cascadeOnDelete();
            $table->string('locale', 2);
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['skill_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skill_translations');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('skill_category_translations');
        Schema::dropIfExists('skill_categories');
    }
};
