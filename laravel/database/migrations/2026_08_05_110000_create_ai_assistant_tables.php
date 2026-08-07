<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Assistant IA (panneau latéral) : conversations + messages persistés, et
 * routines programmées connectées aux skills/prompts existants (D14). Voir
 * la demande utilisateur du 2026-08-05 : icône IA dans la Topbar ouvrant un
 * panneau à 3 onglets (Chat / Routine / Historique).
 *
 * `AiConversation`/`AiMessage` sont des tables neuves plutôt qu'une réutilisation
 * de `ChatConversation`/`ChatMessage` (orphelines, aucune migration existante) —
 * évite toute ambiguïté avec le domaine `Chat` humain (`WorkplaceConversation`).
 *
 * `ai_routines` ne modélise pas un cron générique : `schedule_type` couvre les
 * cas d'usage réels (once/hourly/daily/weekly) avec `run_time`/`day_of_week`,
 * exécuté par la commande `ai:routines:run-due` (voir Kernel::schedule()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->nullable()->constrained('organisations')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 190)->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['organisation_id', 'user_id']);
        });

        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('ai_conversations')->cascadeOnDelete();
            $table->string('role', 20); // user|assistant|system
            $table->longText('content');
            $table->json('context')->nullable();
            $table->unsignedInteger('tokens_used')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['conversation_id', 'created_at']);
        });

        Schema::create('ai_routines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained('organisations')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 190);
            $table->text('description')->nullable();
            $table->foreignId('prompt_id')->nullable()->constrained('prompts')->nullOnDelete();
            $table->foreignId('skill_id')->nullable()->constrained('ai_skills')->nullOnDelete();
            $table->string('schedule_type', 20)->default('once'); // once|hourly|daily|weekly
            $table->time('run_time')->nullable(); // daily|weekly
            $table->unsignedTinyInteger('day_of_week')->nullable(); // 0 (dimanche) .. 6, weekly
            $table->boolean('is_enabled')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->string('last_status', 20)->nullable(); // success|error
            $table->text('last_output')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamps();

            $table->index(['organisation_id', 'is_enabled', 'next_run_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_routines');
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
    }
};
