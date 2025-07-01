<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table pour les notifications de courrier
        Schema::create('mail_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_id')->constrained('mails')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type'); // Type de notification (enum)
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Données additionnelles
            $table->timestamp('read_at')->nullable();
            $table->integer('priority')->default(1); // Priorité de 1 à 5
            $table->timestamp('scheduled_for')->nullable(); // Pour les notifications programmées
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index(['mail_id', 'type']);
            $table->index('priority');
        });

        // Table pour l'historique des courriers
        Schema::create('mail_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_id')->constrained('mails')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action'); // created, updated, deleted, assigned, etc.
            $table->string('field_changed')->nullable(); // Champ modifié
            $table->json('old_value')->nullable(); // Ancienne valeur
            $table->json('new_value')->nullable(); // Nouvelle valeur
            $table->text('description')->nullable(); // Description de l'action
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('location_data')->nullable(); // Organisation, session, etc.
            $table->integer('processing_time')->nullable(); // Temps de traitement en secondes
            $table->json('metadata')->nullable(); // Métadonnées supplémentaires
            $table->timestamps();

            $table->index(['mail_id', 'created_at']);
            $table->index(['user_id', 'action']);
            $table->index('action');
        });

        // Table pour le workflow des courriers
        Schema::create('mail_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_id')->constrained('mails')->onDelete('cascade');
            $table->string('current_status'); // Statut actuel (enum)
            $table->foreignId('current_assignee_id')->nullable()->constrained('users')->onDelete('set null');
            $table->json('workflow_data')->nullable(); // Données du workflow
            $table->boolean('approval_required')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->foreignId('escalated_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('deadline')->nullable();
            $table->integer('auto_escalate_hours')->nullable(); // Escalade automatique après X heures
            $table->boolean('priority_escalation_enabled')->default(false);
            $table->timestamps();

            $table->index(['current_assignee_id', 'current_status']);
            $table->index('deadline');
            $table->index(['approval_required', 'approved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_workflows');
        Schema::dropIfExists('mail_histories');
        Schema::dropIfExists('mail_notifications');
    }
};
