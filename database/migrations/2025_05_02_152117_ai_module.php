<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table principale pour le module d'intelligence
        Schema::create('ai_module', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('configuration')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('is_active');
        });

        // Table pour les modèles d'IA disponibles
        Schema::create('ai_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('provider'); // OpenAI, Anthropic, etc.
            $table->string('version');
            // Champs spécifiques à Ollama - placés directement sans after()
            $table->string('model_family')->nullable();
            $table->bigInteger('parameter_size')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->string('quantization')->nullable();
            $table->timestamp('model_modified_at')->nullable();
            $table->string('digest', 100)->nullable();
            $table->json('model_details')->nullable();
            $table->boolean('supports_streaming')->default(true);
            $table->integer('max_context_length')->nullable();
            $table->decimal('default_temperature', 3, 2)->default(0.70);

            $table->string('api_type'); // chat, embedding, etc.
            $table->json('capabilities'); // Ce que le modèle peut faire
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('is_active');
            $table->index('provider');
            $table->index(['provider', 'is_active']);
            $table->index(['model_family', 'parameter_size']);
        });

        // Table pour les interactions IA (historique des requêtes)
        Schema::create('ai_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('ai_model_id')->constrained('ai_models')->onDelete('cascade');
            $table->text('input');
            $table->text('output');
            $table->json('parameters')->nullable();
            $table->float('tokens_used')->nullable();

            // Statistiques spécifiques à Ollama - placées directement
            $table->bigInteger('total_duration')->nullable();
            $table->bigInteger('load_duration')->nullable();
            $table->bigInteger('prompt_eval_duration')->nullable();
            $table->bigInteger('eval_duration')->nullable();
            $table->integer('prompt_eval_count')->nullable();
            $table->integer('eval_count')->nullable();
            $table->json('context_data')->nullable();
            $table->boolean('was_streamed')->default(false);
            $table->decimal('temperature_used', 3, 2)->nullable();
            $table->text('error_message')->nullable();

            $table->string('module_type')->nullable(); // records, slip, communication, mail
            $table->unsignedBigInteger('module_id')->nullable(); // ID spécifique au module
            $table->string('status')->default('completed');
            $table->string('session_id')->nullable();
            $table->timestamps();

            // Index
            $table->index('user_id');
            $table->index('ai_model_id');
            $table->index(['module_type', 'module_id']);
            $table->index('session_id');
            $table->index(['status', 'created_at']);
            $table->index(['ai_model_id', 'status']);
        });

        Schema::create('ai_model_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_model_id')->constrained()->onDelete('cascade');
            $table->date('metric_date');
            $table->integer('total_interactions')->default(0);
            $table->integer('successful_interactions')->default(0);
            $table->integer('failed_interactions')->default(0);
            $table->bigInteger('total_tokens')->default(0);
            $table->bigInteger('average_response_time')->nullable(); // en millisecondes
            $table->decimal('average_temperature', 3, 2)->nullable();
            $table->json('performance_stats')->nullable();
            $table->timestamps();

            $table->unique(['ai_model_id', 'metric_date']);
            $table->index(['metric_date', 'ai_model_id']);
        });

        Schema::create('ai_conversation_contexts', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 100)->index();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ai_model_id')->constrained()->onDelete('cascade');
            $table->json('context_data'); // Stockage du contexte Ollama
            $table->integer('message_count')->default(0);
            $table->timestamp('last_used_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['session_id', 'user_id']);
            $table->index(['expires_at']);
        });

        // Table pour les chats avec l'IA
        Schema::create('ai_chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->foreignId('ai_model_id')->constrained('ai_models')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index('user_id');
            $table->index('ai_model_id');
            $table->index('is_active');
        });

        // Table pour les messages des chats
        Schema::create('ai_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_chat_id')->constrained('ai_chats')->onDelete('cascade');
            $table->enum('role', ['user', 'assistant', 'system'])->default('user');
            $table->text('content');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index('ai_chat_id');
            $table->index('role');
        });

        // Table pour les ressources utilisées dans les conversations
        Schema::create('ai_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_chat_id')->nullable()->constrained('ai_chats')->onDelete('set null');
            $table->string('resource_type'); // records, slip, communication, mail
            $table->unsignedBigInteger('resource_id');
            $table->json('content_used')->nullable(); // Quelles parties du contenu ont été utilisées
            $table->timestamps();
            $table->index('ai_chat_id');
            $table->index(['resource_type', 'resource_id']);
        });

        // Table unifiée pour toutes les actions IA (remplace ai_text_revisions, ai_keyword_suggestions, etc.)
        Schema::create('ai_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_interaction_id')->constrained('ai_interactions')->onDelete('cascade');
            $table->string('action_type'); // text_revision, keyword_suggestion, classification, description_enhancement, etc.
            $table->string('target_type'); // records, slip, communication, mail
            $table->unsignedBigInteger('target_id');
            $table->string('field_name')->nullable(); // Quel champ est ciblé (si applicable)
            $table->json('original_data'); // Données originales
            $table->json('modified_data'); // Données modifiées ou suggérées
            $table->text('explanation')->nullable(); // Explication de l'action
            $table->json('metadata')->nullable(); // Données supplémentaires spécifiques à l'action
            $table->enum('status', ['pending', 'accepted', 'rejected', 'modified', 'applied'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index('ai_interaction_id');
            $table->index(['target_type', 'target_id']);
            $table->index('action_type');
            $table->index('status');
            $table->index('field_name');
        });

        // Table pour les types d'actions personnalisées
        Schema::create('ai_action_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->json('required_fields')->nullable(); // Champs requis pour ce type d'action
            $table->json('optional_fields')->nullable(); // Champs optionnels pour ce type d'action
            $table->json('validation_rules')->nullable(); // Règles de validation
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index('name');
            $table->index('is_active');
        });

        // Table pour les templates de prompts
        Schema::create('ai_prompt_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('template_content');
            $table->foreignId('action_type_id')->nullable()->constrained('ai_action_types')->onDelete('set null');
            $table->json('variables')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index('action_type_id');
            $table->index('is_active');
            $table->index('created_by');
        });

        // Table pour les retours d'expérience utilisateur sur les actions IA
        Schema::create('ai_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('ai_interaction_id')->constrained('ai_interactions')->onDelete('cascade');
            $table->enum('rating', [1, 2, 3, 4, 5])->nullable();
            $table->text('comments')->nullable();
            $table->boolean('was_helpful')->nullable();
            $table->timestamps();
            $table->index('user_id');
            $table->index('ai_interaction_id');
            $table->index('rating');
        });

        // Table pour les hooks d'intégration du module IA avec d'autres modules
        Schema::create('ai_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('module_name'); // records, slip, communication, mail
            $table->string('event_name'); // create, update, delete, etc.
            $table->string('hook_type'); // before, after
            $table->foreignId('action_type_id')->constrained('ai_action_types')->onDelete('cascade');
            $table->foreignId('ai_prompt_template_id')->nullable()->constrained('ai_prompt_templates')->onDelete('set null');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('configuration')->nullable(); // Configuration spécifique à l'intégration
            $table->timestamps();
            $table->index('module_name');
            $table->index('event_name');
            $table->index('hook_type');
            $table->index('is_active');
        });

        // Table pour les tâches de traitement en arrière-plan
        Schema::create('ai_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_type');
            $table->foreignId('ai_model_id')->constrained('ai_models')->onDelete('cascade');
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->json('parameters')->nullable();
            $table->text('input')->nullable();
            $table->json('result')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index('job_type');
            $table->index('ai_model_id');
            $table->index('status');
        });

        // Table pour les données d'apprentissage personnalisé
        Schema::create('ai_training_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('action_type_id')->constrained('ai_action_types')->onDelete('cascade');
            $table->text('input');
            $table->text('expected_output');
            $table->boolean('is_validated')->default(false);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->index('action_type_id');
            $table->index('is_validated');
        });

        // Table pour stocker les ensembles d'actions groupées
        Schema::create('ai_action_batches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['in_progress', 'completed', 'cancelled'])->default('in_progress');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index('user_id');
            $table->index('status');
        });

        // Table de relation entre batches et actions
        Schema::create('ai_batch_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('ai_action_batches')->onDelete('cascade');
            $table->foreignId('action_id')->constrained('ai_actions')->onDelete('cascade');
            $table->integer('sequence')->default(0);
            $table->timestamps();
            $table->unique(['batch_id', 'action_id']);
            $table->index('batch_id');
            $table->index('action_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_batch_actions');
        Schema::dropIfExists('ai_action_batches');
        Schema::dropIfExists('ai_training_data');
        Schema::dropIfExists('ai_jobs');
        Schema::dropIfExists('ai_integrations');
        Schema::dropIfExists('ai_feedback');
        Schema::dropIfExists('ai_prompt_templates');
        Schema::dropIfExists('ai_action_types');
        Schema::dropIfExists('ai_actions');
        Schema::dropIfExists('ai_resources');
        Schema::dropIfExists('ai_chat_messages');
        Schema::dropIfExists('ai_chats');
        Schema::dropIfExists('ai_conversation_contexts');
        Schema::dropIfExists('ai_model_metrics');
        Schema::dropIfExists('ai_interactions');
        Schema::dropIfExists('ai_models');
        Schema::dropIfExists('ai_module');
    }
};