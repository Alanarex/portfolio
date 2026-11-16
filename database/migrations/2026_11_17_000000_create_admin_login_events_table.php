<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Journal des tentatives de connexion au tableau de bord : adresse IP déjà tronquée à l'écriture,
        // jamais d'adresse e-mail saisie ni de mot de passe.
        Schema::create('admin_login_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->index();
            $table->boolean('succeeded');
            $table->string('ip_masked', 64)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_login_events');
    }
};
