<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_assets', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('project_id')->nullable()->constrained('projects')->cascadeOnDelete();
            $table->string('kind', 20);
            $table->string('disk', 40);
            $table->text('path');
            $table->text('original_filename');
            $table->string('mime_type', 100);
            $table->string('extension', 10);
            $table->unsignedBigInteger('size_bytes');
            $table->string('checksum_sha256', 64);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->boolean('is_public')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('deletion_pending_at')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'is_public', 'sort_order']);
            $table->index(['kind', 'is_public']);
        });

        Schema::create('media_asset_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('media_asset_id')->constrained('media_assets')->cascadeOnDelete();
            $table->string('locale', 2);
            $table->string('alt_text', 240)->nullable();
            $table->text('caption')->nullable();
            $table->timestamps();
            $table->unique(['media_asset_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_asset_translations');
        Schema::dropIfExists('media_assets');
    }
};
