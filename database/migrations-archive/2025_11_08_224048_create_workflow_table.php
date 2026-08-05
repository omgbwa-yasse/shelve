<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Table des définitions de workflow (modèles BPMN)
        Schema::create('workflow_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->longText('bpmn_xml');
            $table->integer('version')->default(1);
            $table->string('status', 20);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('created_by', 'idx_workflow_created');
            $table->index('updated_by', 'idx_workflow_updated');

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        // Table des instances de workflow (exécutions)
        Schema::create('workflow_instances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('definition_id');
            $table->string('name', 190);
            $table->string('status', 20);
            $table->json('current_state');
            $table->unsignedBigInteger('started_by');
            $table->timestamp('started_at')->useCurrent();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->index('definition_id', 'idx_workflow_definition');
            $table->index('started_by', 'idx_workflow_started');
            $table->index('updated_by', 'idx_workflow_updated');
            $table->index('completed_by', 'idx_workflow_completed');

            $table->foreign('definition_id')->references('id')->on('workflow_definitions');
            $table->foreign('started_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('completed_by')->references('id')->on('users');
        });

        // Table unique des tâches (générales et workflow)
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title', 190);
            $table->text('description')->nullable();
            $table->string('status', 20);
            $table->string('priority', 20);
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('workflow_instance_id')->nullable();
            $table->string('task_key', 100)->nullable(); // Référence BPMN (ex: "approval_step")
            $table->json('form_data')->nullable(); // Données du formulaire de la tâche
            $table->integer('sequence_order')->nullable(); // Ordre dans le workflow
            $table->unsignedBigInteger('parent_task_id')->nullable(); // Support des sous-tâches
            $table->string('taskable_type')->nullable(); // Type d'entité liée (polymorphique)
            $table->unsignedBigInteger('taskable_id')->nullable(); // ID de l'entité liée
            $table->timestamp('due_date')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->index('workflow_instance_id', 'idx_task_workflow');
            $table->index('assigned_to', 'idx_task_assigned');
            $table->index('created_by', 'idx_task_created');
            $table->index('updated_by', 'idx_task_updated');
            $table->index('completed_by', 'idx_task_completed');
            $table->index('parent_task_id', 'idx_task_parent');
            $table->index(['taskable_type', 'taskable_id'], 'idx_task_taskable');

            $table->foreign('workflow_instance_id')->references('id')->on('workflow_instances')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('completed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('parent_task_id')->references('id')->on('tasks')->onDelete('cascade');
        });

        // Table des transitions de workflow (règles de passage)
        Schema::create('workflow_transitions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('definition_id');
            $table->string('from_task_key', 100); // Clé BPMN de la tâche source
            $table->string('to_task_key', 100); // Clé BPMN de la tâche destination
            $table->string('name', 100); // Nom de la transition
            $table->text('condition')->nullable(); // Condition pour exécuter la transition (JSON)
            $table->integer('sequence_order')->default(0); // Ordre de priorité
            $table->boolean('is_default')->default(false); // Transition par défaut
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('definition_id', 'idx_transition_definition');
            $table->index('from_task_key', 'idx_transition_from');
            $table->index('to_task_key', 'idx_transition_to');

            $table->foreign('definition_id')->references('id')->on('workflow_definitions')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        // Table de l'historique des tâches
        Schema::create('task_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->string('field_changed', 50); // Champ modifié
            $table->text('old_value')->nullable(); // Ancienne valeur
            $table->text('new_value')->nullable(); // Nouvelle valeur
            $table->string('action', 50); // Type d'action: created, updated, completed, assigned, etc.
            $table->unsignedBigInteger('changed_by');
            $table->timestamp('changed_at')->useCurrent();

            $table->index('task_id', 'idx_history_task');
            $table->index('changed_by', 'idx_history_user');
            $table->index('changed_at', 'idx_history_date');

            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('changed_by')->references('id')->on('users');
        });

        // Table des pièces jointes aux tâches (association polymorphique)
        Schema::create('task_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->enum('attachable_type', ['Book', 'RecordPhysical', 'Document', 'Folder', 'Artifact', 'Collection']);
            $table->unsignedBigInteger('attachable_id'); // ID de l'entité attachée
            $table->text('description')->nullable(); // Description de l'association
            $table->unsignedBigInteger('attached_by');
            $table->timestamp('attached_at')->useCurrent();

            $table->index('task_id', 'idx_attachment_task');
            $table->index(['attachable_type', 'attachable_id'], 'idx_attachment_attachable');
            $table->index('attached_by', 'idx_attachment_user');

            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('attached_by')->references('id')->on('users');
        });

        // Table des rappels de tâches
        Schema::create('task_reminders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->timestamp('remind_at'); // Date/heure du rappel
            $table->string('reminder_type', 50); // email, notification, sms, etc.
            $table->text('message')->nullable(); // Message personnalisé
            $table->boolean('is_sent')->default(false); // Statut d'envoi
            $table->timestamp('sent_at')->nullable(); // Date d'envoi effective
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_at')->useCurrent();

            $table->index('task_id', 'idx_reminder_task');
            $table->index('remind_at', 'idx_reminder_date');
            $table->index('is_sent', 'idx_reminder_status');

            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users');
        });

        // Table des commentaires sur les tâches
        Schema::create('task_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->text('comment');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at')->nullable(); // Soft delete

            $table->index('task_id', 'idx_comment_task');
            $table->index('user_id', 'idx_comment_user');
            $table->index('created_at', 'idx_comment_date');

            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        // Table des observateurs de tâches (watchers)
        Schema::create('task_watchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('notify_on_update')->default(true); // Notifier sur mise à jour
            $table->boolean('notify_on_comment')->default(true); // Notifier sur nouveau commentaire
            $table->boolean('notify_on_completion')->default(true); // Notifier sur complétion
            $table->unsignedBigInteger('added_by');
            $table->timestamp('added_at')->useCurrent();

            $table->unique(['task_id', 'user_id'], 'unique_task_watcher'); // Un utilisateur ne peut observer qu'une fois
            $table->index('task_id', 'idx_watcher_task');
            $table->index('user_id', 'idx_watcher_user');

            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('added_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_watchers');
        Schema::dropIfExists('task_comments');
        Schema::dropIfExists('task_reminders');
        Schema::dropIfExists('task_attachments');
        Schema::dropIfExists('task_history');
        Schema::dropIfExists('workflow_transitions');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('workflow_instances');
        Schema::dropIfExists('workflow_definitions');
    }
};
