<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_experiences', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('organization', 160);
            $table->unsignedSmallInteger('start_year');
            $table->unsignedTinyInteger('start_month')->nullable();
            $table->unsignedSmallInteger('end_year')->nullable();
            $table->unsignedTinyInteger('end_month')->nullable();
            $table->boolean('is_current')->default(false);
            $table->string('status', 20)->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'sort_order']);
        });

        Schema::create('career_experience_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('experience_id')->constrained('career_experiences')->cascadeOnDelete();
            $table->string('locale', 2);
            $table->string('role', 180);
            $table->string('employment_type', 100)->nullable();
            $table->string('location', 160)->nullable();
            $table->text('summary')->nullable();
            $table->json('highlights');
            $table->timestamps();
            $table->unique(['experience_id', 'locale']);
        });

        Schema::create('career_achievements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('experience_id')->constrained('career_experiences')->cascadeOnDelete();
            $table->string('key', 100);
            $table->string('status', 20)->default('draft');
            $table->boolean('is_quantified')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->string('source_reference')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['experience_id', 'key']);
            $table->index(['experience_id', 'status', 'is_verified', 'sort_order'], 'career_achievements_public_idx');
        });

        Schema::create('career_achievement_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('achievement_id')->constrained('career_achievements')->cascadeOnDelete();
            $table->string('locale', 2);
            $table->text('statement');
            $table->timestamps();
            $table->unique(['achievement_id', 'locale']);
        });

        Schema::create('education_records', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('institution', 160);
            $table->unsignedSmallInteger('start_year');
            $table->unsignedTinyInteger('start_month')->nullable();
            $table->unsignedSmallInteger('end_year')->nullable();
            $table->unsignedTinyInteger('end_month')->nullable();
            $table->string('status', 20)->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'sort_order']);
        });

        Schema::create('education_record_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('education_record_id')->constrained('education_records')->cascadeOnDelete();
            $table->string('locale', 2);
            $table->string('program', 200);
            $table->string('level', 160)->nullable();
            $table->string('location', 160)->nullable();
            $table->text('summary')->nullable();
            $table->timestamps();
            $table->unique(['education_record_id', 'locale'], 'education_record_locale_unique');
        });

        Schema::create('certifications', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('issuer', 160)->nullable();
            $table->string('credential_id', 160)->nullable();
            $table->string('verification_url', 2048)->nullable();
            $table->unsignedSmallInteger('issue_year');
            $table->unsignedTinyInteger('issue_month')->nullable();
            $table->string('result', 120)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->string('source_reference')->nullable();
            $table->string('status', 20)->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'sort_order']);
        });

        Schema::create('certification_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('certification_id')->constrained('certifications')->cascadeOnDelete();
            $table->string('locale', 2);
            $table->string('name', 180);
            $table->string('skill_label', 180)->nullable();
            $table->timestamps();
            $table->unique(['certification_id', 'locale']);
        });

        Schema::create('language_proficiencies', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('language_code', 10);
            $table->string('proficiency_kind', 20);
            $table->string('cefr_level', 5)->nullable();
            $table->string('status', 20)->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'sort_order']);
        });

        Schema::create('language_proficiency_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('language_proficiency_id')->constrained('language_proficiencies')->cascadeOnDelete();
            $table->string('locale', 2);
            $table->string('name', 100);
            $table->string('proficiency_label', 160);
            $table->string('evidence', 240)->nullable();
            $table->timestamps();
            $table->unique(['language_proficiency_id', 'locale'], 'language_proficiency_locale_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('language_proficiency_translations');
        Schema::dropIfExists('language_proficiencies');
        Schema::dropIfExists('certification_translations');
        Schema::dropIfExists('certifications');
        Schema::dropIfExists('education_record_translations');
        Schema::dropIfExists('education_records');
        Schema::dropIfExists('career_achievement_translations');
        Schema::dropIfExists('career_achievements');
        Schema::dropIfExists('career_experience_translations');
        Schema::dropIfExists('career_experiences');
    }
};
