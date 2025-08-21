<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ============ WORKFLOWS SIMPLIFIÉS ============

        // Templates de workflows
        Schema::create('workflow_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // Étapes des workflows
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_template_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->integer('order_index');
            $table->enum('step_type', ['manual', 'automatic', 'approval'])->default('manual');
            $table->integer('estimated_hours')->nullable();
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            $table->unique(['workflow_template_id', 'order_index']);
        });

        // Qui peut faire quoi dans chaque étape
        Schema::create('workflow_step_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_step_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assignee_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assignee_organisation_id')->nullable()->constrained('organisations')->nullOnDelete();
            $table->timestamps();
        });

        // Workflows en cours
        Schema::create('workflow_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mail_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('current_step_id')->nullable()->constrained('workflow_steps')->nullOnDelete();
            $table->foreignId('initiated_by')->constrained('users')->cascadeOnDelete();
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->datetime('due_date')->nullable();
            $table->timestamps();

            $table->index(['status', 'due_date']);
            $table->index(['mail_id', 'status']);
        });

        // Historique des étapes
        Schema::create('workflow_step_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_instance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workflow_step_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'skipped'])->default('pending');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to_organisation_id')->nullable()->constrained('organisations')->nullOnDelete();
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['workflow_instance_id', 'status']);
            $table->index(['assigned_to_user_id', 'status']);
        });

        // ============ TÂCHES SIMPLIFIÉES ============

        // Catégories de tâches
        Schema::create('task_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('color', 7)->default('#007bff');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tâches
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->enum('status', ['todo', 'in_progress', 'done', 'cancelled'])->default('todo');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->datetime('due_date')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->integer('estimated_hours')->nullable();

            // Relations
            $table->foreignId('category_id')->nullable()->constrained('task_categories')->nullOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to_organisation_id')->nullable()->constrained('organisations')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mail_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('workflow_step_instance_id')->nullable()->constrained('workflow_step_instances')->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'assigned_to_user_id']);
            $table->index(['due_date', 'status']);
            $table->index(['mail_id', 'status']);
        });

        // Commentaires sur les tâches
        Schema::create('task_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('comment');
            $table->timestamps();

            $table->index(['task_id', 'created_at']);
        });

        // ============ AMÉLIORATION DE LA TABLE MAILS ============

        Schema::table('mails', function (Blueprint $table) {
            $table->datetime('expected_response_date')->nullable()->after('date');
            $table->datetime('actual_response_date')->nullable()->after('expected_response_date');
            $table->enum('urgency_level', ['low', 'medium', 'high', 'critical'])->default('medium')->after('priority_id');
            $table->text('processing_notes')->nullable()->after('description');
            $table->foreignId('current_assignee_user_id')->nullable()->constrained('users')->nullOnDelete()->after('recipient_organisation_id');
            $table->foreignId('current_assignee_organisation_id')->nullable()->constrained('organisations')->nullOnDelete()->after('current_assignee_user_id');
            $table->foreignId('workflow_instance_id')->nullable()->constrained('workflow_instances')->nullOnDelete();

            $table->index(['status', 'current_assignee_user_id']);
            $table->index(['current_assignee_organisation_id', 'status']);
        });
    }

    public function down(): void
    {
        // Supprimer les colonnes ajoutées à la table mails
        Schema::table('mails', function (Blueprint $table) {
            $table->dropForeign(['workflow_instance_id']);
            $table->dropForeign(['current_assignee_user_id']);
            $table->dropForeign(['current_assignee_organisation_id']);
            $table->dropColumn([
                'expected_response_date',
                'actual_response_date',
                'urgency_level',
                'processing_notes',
                'current_assignee_user_id',
                'current_assignee_organisation_id',
                'workflow_instance_id'
            ]);
        });

        // Supprimer les tables dans l'ordre inverse
        Schema::dropIfExists('task_comments');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('task_categories');
        Schema::dropIfExists('workflow_step_instances');
        Schema::dropIfExists('workflow_instances');
        Schema::dropIfExists('workflow_step_assignments');
        Schema::dropIfExists('workflow_steps');
        Schema::dropIfExists('workflow_templates');
    }
};
