<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('prompts')) {
            Schema::create('prompts', function (Blueprint $table) {
                $table->id();
                $table->string('title', 100)->nullable();
                $table->longText('content');
                $table->boolean('is_system')->default(false)->index();
                $table->foreignId('organisation_id')->nullable()->constrained('organisations')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                // Recherche plein texte et unicité contextuelle
                $table->fullText(['title', 'content']);
                $table->unique(['title', 'is_system', 'organisation_id', 'user_id'], 'prompts_unique_title_scope');
            });
        } else {
            // Si la table existe déjà, vérifier et ajouter les colonnes manquantes
            Schema::table('prompts', function (Blueprint $table) {
                if (!Schema::hasColumn('prompts', 'is_system')) {
                    $table->boolean('is_system')->default(false)->index();
                }
                if (!Schema::hasColumn('prompts', 'organisation_id')) {
                    $table->foreignId('organisation_id')->nullable()->constrained('organisations')->nullOnDelete();
                }
                if (!Schema::hasColumn('prompts', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                }
            });
        }

        if (!Schema::hasTable('prompt_transactions')) {
            Schema::create('prompt_transactions', function (Blueprint $table) {
                $table->id();
            $table->foreignId('prompt_id')->nullable()->constrained('prompts')->nullOnDelete();
            $table->timestampTz('started_at')->nullable()->index();
            $table->timestampTz('finished_at')->nullable()->index();
            $table->string('model', 191)->nullable()->index();

            // Fournisseur de modèle (openai, ollama, azure, ...)
            $table->string('model_provider', 50)->nullable()->index();
            $table->foreignId('organisation_id')->nullable()->constrained('organisations')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('entity', ['record', 'mail', 'communication', 'slip_record'])->index();
            $table->json('entity_ids')->nullable(true);

            // Statut avec valeur par défaut
            $table->enum('status', ['started','succeeded','failed','cancelled'])->default('started')->index();

            // Compteurs de tokens et message d'erreur éventuel
            $table->unsignedInteger('tokens_input')->nullable();
            $table->unsignedInteger('tokens_output')->nullable();
            $table->text('error_message')->nullable();

            // Latence en millisecondes pour le suivi de performance
            $table->unsignedInteger('latency_ms')->nullable();

            // Indexes composés pour accélérer les requêtes usuelles
            $table->index(['organisation_id', 'entity', 'started_at'], 'pt_org_entity_started_idx');
            $table->index(['prompt_id', 'status', 'started_at'], 'pt_prompt_status_started_idx');

            $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prompt_transactions');
        Schema::dropIfExists('prompts');
    }
};
