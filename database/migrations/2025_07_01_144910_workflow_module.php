<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ============ SYSTÈME DE WORKFLOWS ============

        // Templates de workflows
        Schema::create('workflow_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('category', 50)->default('mail_processing');
            $table->boolean('is_active')->default(true);
            $table->json('configuration')->nullable()->comment('Configuration du workflow');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['category', 'is_active']);
        });

        // Étapes des workflows
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_template_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->integer('order_index');
            $table->enum('step_type', ['manual', 'automatic', 'approval', 'notification', 'conditional'])->default('manual');
            $table->json('configuration')->nullable()->comment('Configuration de l\'étape');
            $table->integer('estimated_duration')->nullable()->comment('Durée estimée en minutes');
            $table->boolean('is_required')->default(true);
            $table->boolean('can_be_skipped')->default(false);
            $table->json('conditions')->nullable()->comment('Conditions pour cette étape');
            $table->timestamps();

            $table->unique(['workflow_template_id', 'order_index']);
            $table->index(['workflow_template_id', 'step_type']);
        });

        // Assignations d'étapes (qui peut faire quoi)
        Schema::create('workflow_step_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_step_id')->constrained()->cascadeOnDelete();
            $table->enum('assignee_type', ['user', 'organisation', 'role', 'department', 'auto']);
            $table->foreignId('assignee_user_id')->nullable()->constrained('users')->nullOnDelete()->comment('Utilisateur assigné');
            $table->foreignId('assignee_organisation_id')->nullable()->constrained('organisations')->nullOnDelete()->comment('Organisation assignée');
            $table->foreignId('assignee_role_id')->nullable()->comment('Role assigné (si vous avez une table roles)');
            $table->json('assignment_rules')->nullable()->comment('Règles d\'assignation automatique');
            $table->boolean('allow_reassignment')->default(true)->comment('Permet la réassignation');
            $table->timestamps();

            $table->index(['workflow_step_id', 'assignee_type']);
            $table->index(['assignee_user_id', 'assignee_organisation_id']);
        });

        // Instances de workflows (workflows en cours)
        Schema::create('workflow_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mail_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled', 'on_hold'])->default('pending');
            $table->foreignId('current_step_id')->nullable()->constrained('workflow_steps')->nullOnDelete();
            $table->foreignId('initiated_by')->constrained('users')->cascadeOnDelete();
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->datetime('due_date')->nullable();
            $table->json('context_data')->nullable()->comment('Données contextuelles du workflow');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'due_date']);
            $table->index(['mail_id', 'status']);
            $table->index(['current_step_id', 'status']); // Index ajouté
        });

        // Historique des étapes de workflow
        Schema::create('workflow_step_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_instance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workflow_step_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'skipped', 'failed'])->default('pending');

            // AFFECTATION FLEXIBLE pour les étapes de workflow
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete()->comment('Utilisateur assigné à cette étape');
            $table->foreignId('assigned_to_organisation_id')->nullable()->constrained('organisations')->nullOnDelete()->comment('Organisation assignée à cette étape');
            $table->enum('assignment_type', ['organisation', 'user', 'both'])->default('user')->comment('Type d\'assignation de l\'étape');

            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->datetime('due_date')->nullable();
            $table->json('input_data')->nullable();
            $table->json('output_data')->nullable();
            $table->text('notes')->nullable();
            $table->text('failure_reason')->nullable();
            $table->text('assignment_notes')->nullable()->comment('Notes sur l\'assignation de l\'étape');
            $table->timestamps();

            $table->index(['workflow_instance_id', 'status']);
            $table->index(['assigned_to_user_id', 'status']);
            $table->index(['assigned_to_organisation_id', 'status']);
            $table->index(['assignment_type', 'status']);
        });

        // ============ SYSTÈME DE TÂCHES ============

        // Catégories de tâches
        Schema::create('task_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('color', 7)->default('#007bff')->comment('Couleur hexadécimale');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tâches
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->enum('status', ['todo', 'in_progress', 'review', 'done', 'cancelled'])->default('todo');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->datetime('due_date')->nullable();
            $table->datetime('start_date')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->integer('estimated_hours')->nullable();
            $table->integer('actual_hours')->nullable();
            $table->integer('progress_percentage')->default(0); // Contrainte CHECK ajoutée plus bas

            // Relations
            $table->foreignId('category_id')->nullable()->constrained('task_categories')->nullOnDelete();

            // AFFECTATION FLEXIBLE : Organisation et/ou Utilisateur
            $table->foreignId('assigned_to_organisation_id')->nullable()->constrained('organisations')->nullOnDelete()->comment('Organisation assignée');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete()->comment('Utilisateur assigné');
            $table->enum('assignment_type', ['organisation', 'user', 'both'])->default('user')->comment('Type d\'assignation');

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mail_id')->nullable()->constrained()->nullOnDelete()->comment('Tâche liée à un courrier');
            $table->foreignId('workflow_step_instance_id')->nullable()->constrained('workflow_step_instances')->nullOnDelete();
            $table->foreignId('parent_task_id')->nullable()->constrained('tasks')->nullOnDelete()->comment('Sous-tâche');

            // Métadonnées
            $table->json('tags')->nullable();
            $table->json('custom_fields')->nullable();
            $table->text('completion_notes')->nullable();
            $table->text('assignment_notes')->nullable()->comment('Notes sur l\'assignation');

            $table->timestamps();

            // Index améliorés
            $table->index(['status', 'assigned_to_user_id']);
            $table->index(['status', 'assigned_to_organisation_id']);
            $table->index(['assignment_type', 'status']);
            $table->index(['due_date', 'status', 'priority']); // Index composé amélioré
            $table->index(['mail_id', 'status']);
            $table->index(['created_by', 'status']);
            $table->index(['parent_task_id', 'status']);
        });

        // Dépendances entre tâches
        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('depends_on_task_id')->constrained('tasks')->cascadeOnDelete();
            $table->enum('dependency_type', ['finish_to_start', 'start_to_start', 'finish_to_finish', 'start_to_finish'])->default('finish_to_start');
            $table->integer('lag_days')->default(0)->comment('Délai en jours');
            $table->timestamps();

            $table->unique(['task_id', 'depends_on_task_id']);
            $table->index(['depends_on_task_id', 'dependency_type']);
        });

        // Assignations multiples de tâches - CORRIGÉE
        Schema::create('task_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();

            // AFFECTATION FLEXIBLE : Peut être un utilisateur ou une organisation
            $table->foreignId('assignee_user_id')->nullable()->constrained('users')->cascadeOnDelete()->comment('Utilisateur assigné');
            $table->foreignId('assignee_organisation_id')->nullable()->constrained('organisations')->cascadeOnDelete()->comment('Organisation assignée');
            $table->enum('assignee_type', ['user', 'organisation'])->comment('Type d\'assigné');

            $table->enum('role', ['assignee', 'reviewer', 'observer', 'collaborator'])->default('assignee');
            $table->integer('allocation_percentage')->default(100);
            $table->datetime('assigned_at')->useCurrent();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete()->comment('Qui a fait l\'assignation');
            $table->text('assignment_reason')->nullable()->comment('Raison de l\'assignation');
            $table->timestamps();

            $table->index(['task_id', 'assignee_type']);
            $table->index(['assignee_user_id', 'role']);
            $table->index(['assignee_organisation_id', 'role']);

            // Contrainte d'unicité corrigée - séparée par type
            $table->unique(['task_id', 'assignee_user_id', 'role'], 'task_user_role_unique');
            $table->unique(['task_id', 'assignee_organisation_id', 'role'], 'task_org_role_unique');
        });

        // Historique des assignations et délégations
        Schema::create('task_assignment_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();

            // Ancienne assignation
            $table->foreignId('previous_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('previous_organisation_id')->nullable()->constrained('organisations')->nullOnDelete();

            // Nouvelle assignation
            $table->foreignId('new_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('new_organisation_id')->nullable()->constrained('organisations')->nullOnDelete();

            $table->enum('action_type', ['assign', 'reassign', 'delegate', 'unassign', 'auto_assign'])->comment('Type d\'action');
            $table->foreignId('performed_by')->constrained('users')->cascadeOnDelete()->comment('Qui a effectué l\'action');
            $table->text('reason')->nullable()->comment('Raison du changement');
            $table->datetime('effective_date')->useCurrent()->comment('Date d\'effet');
            $table->datetime('expiry_date')->nullable()->comment('Date d\'expiration pour les délégations');
            $table->boolean('is_temporary')->default(false)->comment('Assignation temporaire');

            $table->timestamps();

            $table->index(['task_id', 'action_type']);
            $table->index(['performed_by', 'created_at']);
            $table->index(['effective_date', 'expiry_date']);
        });

        // Délégations d'organisations (pour gérer les hiérarchies)
        Schema::create('organisation_delegations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegating_organisation_id')->constrained('organisations')->cascadeOnDelete()->comment('Organisation qui délègue');
            $table->foreignId('delegate_organisation_id')->constrained('organisations')->cascadeOnDelete()->comment('Organisation déléguée');
            $table->foreignId('delegated_by_user_id')->constrained('users')->cascadeOnDelete()->comment('Utilisateur qui a créé la délégation');

            $table->datetime('start_date')->useCurrent();
            $table->datetime('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('scope')->nullable()->comment('Portée de la délégation');
            $table->json('permissions')->nullable()->comment('Permissions déléguées');
            $table->text('reason')->nullable();

            $table->timestamps();

            $table->index(['delegating_organisation_id', 'is_active']);
            $table->index(['delegate_organisation_id', 'is_active']);
            $table->index(['start_date', 'end_date']);

            // Empêcher la délégation circulaire
            $table->unique(['delegating_organisation_id', 'delegate_organisation_id', 'start_date'], 'org_delegation_unique');
        });

        // Commentaires sur les tâches
        Schema::create('task_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('comment');
            $table->enum('type', ['comment', 'status_change', 'assignment_change', 'system'])->default('comment');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['task_id', 'created_at']);
            $table->index(['user_id', 'type']);
        });

        // ============ SYSTÈME DE SUIVI ET NOTIFICATIONS ============

        // Événements du système
        Schema::create('mail_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 50); // created, updated, assigned, completed, etc.
            $table->text('description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['mail_id', 'event_type']);
            $table->index(['user_id', 'created_at']);
            $table->index(['event_type', 'created_at']);
        });

        // Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50); // mail_assigned, task_due, workflow_step, etc.
            $table->string('title', 200);
            $table->text('message');
            $table->json('data')->nullable()->comment('Données contextuelles');
            $table->datetime('read_at')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->string('action_url')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index(['type', 'created_at']);
            $table->index(['priority', 'read_at']);
        });

        // Abonnements aux notifications
        Schema::create('notification_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 50);
            $table->enum('channel', ['email', 'sms', 'push', 'in_app'])->default('in_app');
            $table->boolean('is_active')->default(true);
            $table->json('conditions')->nullable()->comment('Conditions pour déclencher la notification');
            $table->timestamps();

            $table->unique(['user_id', 'event_type', 'channel']);
            $table->index(['event_type', 'is_active']);
        });

        // ============ RAPPORTS ET STATISTIQUES ============

        // Métriques de performance - TYPE AMÉLIORÉ
        Schema::create('mail_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_id')->constrained()->cascadeOnDelete();
            $table->date('metric_date');
            $table->string('metric_type', 50); // processing_time, response_time, etc.
            $table->decimal('value', 15, 4); // Précision améliorée
            $table->string('unit', 20)->nullable(); // hours, days, count, etc.
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['mail_id', 'metric_date', 'metric_type']);
            $table->index(['metric_type', 'metric_date']);
            $table->index(['metric_date', 'value']);
        });

        // Templates d'emails
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('subject', 200);
            $table->text('body_html');
            $table->text('body_text')->nullable();
            $table->string('category', 50)->default('general');
            $table->json('variables')->nullable()->comment('Variables disponibles dans le template');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['category', 'is_active']);
            $table->unique(['name', 'category']);
        });

        // ============ AMÉLIORATION DE LA TABLE MAILS ============

        // Maintenant on peut ajouter la référence aux workflows
        Schema::table('mails', function (Blueprint $table) {
            $table->datetime('expected_response_date')->nullable()->after('date');
            $table->datetime('actual_response_date')->nullable()->after('expected_response_date');
            $table->enum('urgency_level', ['low', 'medium', 'high', 'critical'])->default('medium')->after('priority_id');
            $table->integer('estimated_processing_time')->nullable()->comment('En minutes')->after('urgency_level');
            $table->text('processing_notes')->nullable()->after('description');
            $table->json('metadata')->nullable()->comment('Données supplémentaires flexibles')->after('processing_notes');
            $table->foreignId('current_assignee_user_id')->nullable()->constrained('users')->nullOnDelete()->after('recipient_organisation_id');
            $table->foreignId('current_assignee_organisation_id')->nullable()->constrained('organisations')->nullOnDelete()->after('current_assignee_user_id');
            $table->foreignId('workflow_instance_id')->nullable()->constrained('workflow_instances')->nullOnDelete();

            // Index composés pour les requêtes courantes
            $table->index(['status', 'priority_id', 'expected_response_date'], 'mails_dashboard_index');
            $table->index(['current_assignee_user_id', 'status']);
            $table->index(['current_assignee_organisation_id', 'status']);
        });

        // ============ CONTRAINTES CHECK (PostgreSQL/MySQL 8.0+) ============

        // Ajouter les contraintes après la création des tables
        if (config('database.default') === 'pgsql') {
            // PostgreSQL
            DB::statement('ALTER TABLE tasks ADD CONSTRAINT progress_percentage_check CHECK (progress_percentage >= 0 AND progress_percentage <= 100)');
            DB::statement('ALTER TABLE tasks ADD CONSTRAINT assignment_type_check CHECK (
                (assignment_type = \'user\' AND assigned_to_user_id IS NOT NULL) OR
                (assignment_type = \'organisation\' AND assigned_to_organisation_id IS NOT NULL) OR
                (assignment_type = \'both\' AND assigned_to_user_id IS NOT NULL AND assigned_to_organisation_id IS NOT NULL)
            )');
            DB::statement('ALTER TABLE task_assignments ADD CONSTRAINT allocation_percentage_check CHECK (allocation_percentage > 0 AND allocation_percentage <= 100)');
        } elseif (config('database.default') === 'mysql') {
            // MySQL 8.0+
            DB::statement('ALTER TABLE tasks ADD CONSTRAINT progress_percentage_check CHECK (progress_percentage >= 0 AND progress_percentage <= 100)');
            DB::statement('ALTER TABLE task_assignments ADD CONSTRAINT allocation_percentage_check CHECK (allocation_percentage > 0 AND allocation_percentage <= 100)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les contraintes CHECK d'abord (si supportées)
        if (config('database.default') === 'pgsql') {
            DB::statement('ALTER TABLE tasks DROP CONSTRAINT IF EXISTS progress_percentage_check');
            DB::statement('ALTER TABLE tasks DROP CONSTRAINT IF EXISTS assignment_type_check');
            DB::statement('ALTER TABLE task_assignments DROP CONSTRAINT IF EXISTS allocation_percentage_check');
        } elseif (config('database.default') === 'mysql') {
            DB::statement('ALTER TABLE tasks DROP CHECK progress_percentage_check');
            DB::statement('ALTER TABLE task_assignments DROP CHECK allocation_percentage_check');
        }

        // Supprimer les colonnes ajoutées à la table mails
        Schema::table('mails', function (Blueprint $table) {
            $table->dropForeign(['workflow_instance_id']);
            $table->dropForeign(['current_assignee_user_id']);
            $table->dropForeign(['current_assignee_organisation_id']);
            $table->dropIndex('mails_dashboard_index');
            $table->dropColumn([
                'expected_response_date',
                'actual_response_date',
                'urgency_level',
                'estimated_processing_time',
                'processing_notes',
                'metadata',
                'current_assignee_user_id',
                'current_assignee_organisation_id',
                'workflow_instance_id'
            ]);
        });

        // Supprimer les nouvelles tables dans l'ordre inverse
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('mail_metrics');
        Schema::dropIfExists('notification_subscriptions');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('mail_events');
        Schema::dropIfExists('task_comments');
        Schema::dropIfExists('organisation_delegations');
        Schema::dropIfExists('task_assignment_history');
        Schema::dropIfExists('task_assignments');
        Schema::dropIfExists('task_dependencies');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('task_categories');
        Schema::dropIfExists('workflow_step_instances');
        Schema::dropIfExists('workflow_instances');
        Schema::dropIfExists('workflow_step_assignments');
        Schema::dropIfExists('workflow_steps');
        Schema::dropIfExists('workflow_templates');
    }
};
