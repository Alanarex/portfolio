<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 50)->unique();
            $table->string('display_name', 120);
            $table->boolean('is_published')->default(false)->index();
            $table->boolean('show_location')->default(false);
            $table->boolean('show_availability')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('profile_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->string('locale', 2);
            $table->json('professional_titles');
            $table->text('summary');
            $table->string('location_label', 160)->nullable();
            $table->string('availability', 240)->nullable();
            $table->text('biography');
            $table->timestamps();
            $table->unique(['profile_id', 'locale']);
        });

        Schema::create('cv_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->string('locale', 2);
            $table->string('label', 120);
            $table->string('version_label', 50);
            $table->string('original_filename')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('checksum_sha256', 64)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->unique(['profile_id', 'locale', 'version_label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_versions');
        Schema::dropIfExists('profile_translations');
        Schema::dropIfExists('profiles');
    }
};
