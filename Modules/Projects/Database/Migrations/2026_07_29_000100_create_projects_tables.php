<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('slug', 120)->unique();
            $table->string('lifecycle_status', 24)->default('unspecified');
            $table->json('technologies');
            $table->unsignedSmallInteger('start_year')->nullable();
            $table->unsignedTinyInteger('start_month')->nullable();
            $table->unsignedSmallInteger('end_year')->nullable();
            $table->unsignedTinyInteger('end_month')->nullable();
            $table->boolean('is_ongoing')->default(false);
            $table->string('repository_visibility', 12)->default('none');
            $table->text('repository_url')->nullable();
            $table->boolean('show_repository')->default(false);
            $table->text('demo_url')->nullable();
            $table->boolean('show_demo')->default(false);
            $table->string('publication_status', 20)->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('featured_order')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('source_reference')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('deletion_pending_at')->nullable();
            $table->timestamps();
            $table->index(['publication_status', 'sort_order']);
            $table->index(['publication_status', 'is_featured', 'featured_order'], 'projects_featured_idx');
        });

        Schema::create('project_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('locale', 2);
            $table->string('title', 180);
            $table->text('summary');
            $table->string('role', 180);
            $table->string('seo_title', 180)->nullable();
            $table->string('seo_description', 320)->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'locale']);
        });

        Schema::create('case_study_sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('type', 24);
            $table->boolean('is_public')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['project_id', 'type']);
            $table->index(['project_id', 'is_public', 'sort_order']);
        });

        Schema::create('case_study_section_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('case_study_section_id')->constrained('case_study_sections')->cascadeOnDelete();
            $table->string('locale', 2);
            $table->string('heading', 180);
            $table->text('body');
            $table->timestamps();
            $table->unique(['case_study_section_id', 'locale'], 'case_study_section_locale_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_study_section_translations');
        Schema::dropIfExists('case_study_sections');
        Schema::dropIfExists('project_translations');
        Schema::dropIfExists('projects');
    }
};
