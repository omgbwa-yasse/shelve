<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Cette migration simplifie la structure du module workflow selon la nouvelle spécification.
     */
    public function up(): void
    {
        Schema::table('workflow_templates', function (Blueprint $table) {
            if (Schema::hasColumn('workflow_templates', 'category')) {
                $table->dropColumn('category');
            }
            if (Schema::hasColumn('workflow_templates', 'configuration')) {
                $table->dropColumn('configuration');
            }
        });

        // 2. Simplification de la table workflow_steps
        Schema::table('workflow_steps', function (Blueprint $table) {
            if (Schema::hasColumn('workflow_steps', 'estimated_duration') && !Schema::hasColumn('workflow_steps', 'estimated_hours')) {
                $table->renameColumn('estimated_duration', 'estimated_hours');
            }

            $table->string('step_type', 20)->change();

            if (Schema::hasColumn('workflow_steps', 'configuration')) {
                $table->dropColumn('configuration');
            }
            if (Schema::hasColumn('workflow_steps', 'can_be_skipped')) {
                $table->dropColumn('can_be_skipped');
            }
            if (Schema::hasColumn('workflow_steps', 'conditions')) {
                $table->dropColumn('conditions');
            }
        });

        // 3. Simplification de la table workflow_step_assignments
        Schema::table('workflow_step_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('workflow_step_assignments', 'assignee_type')) {
                $table->dropColumn('assignee_type');
            }
            if (Schema::hasColumn('workflow_step_assignments', 'assignee_role_id')) {
                $table->dropColumn('assignee_role_id');
            }
            if (Schema::hasColumn('workflow_step_assignments', 'assignment_rules')) {
                $table->dropColumn('assignment_rules');
            }
            if (Schema::hasColumn('workflow_step_assignments', 'allow_reassignment')) {
                $table->dropColumn('allow_reassignment');
            }
        });

        // 4. Simplification de la table workflow_instances
        Schema::table('workflow_instances', function (Blueprint $table) {
            if (Schema::hasColumn('workflow_instances', 'context_data')) {
                $table->dropColumn('context_data');
            }
            if (Schema::hasColumn('workflow_instances', 'notes')) {
                $table->dropColumn('notes');
            }
        });

        // 5. Simplification de la table workflow_step_instances
        Schema::table('workflow_step_instances', function (Blueprint $table) {
            if (Schema::hasColumn('workflow_step_instances', 'assignment_type')) {
                $table->dropColumn('assignment_type');
            }
            if (Schema::hasColumn('workflow_step_instances', 'input_data')) {
                $table->dropColumn('input_data');
            }
            if (Schema::hasColumn('workflow_step_instances', 'output_data')) {
                $table->dropColumn('output_data');
            }
            if (Schema::hasColumn('workflow_step_instances', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('workflow_step_instances', 'assignment_notes')) {
                $table->dropColumn('assignment_notes');
            }
        });

        // 6. Simplification de la table tasks
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'assignment_type')) {
                $table->dropColumn('assignment_type');
            }
            if (Schema::hasColumn('tasks', 'tags')) {
                $table->dropColumn('tags');
            }
            if (Schema::hasColumn('tasks', 'custom_fields')) {
                $table->dropColumn('custom_fields');
            }
            if (Schema::hasColumn('tasks', 'assignment_notes')) {
                $table->dropColumn('assignment_notes');
            }
            if (Schema::hasColumn('tasks', 'progress_percentage')) {
                $table->dropColumn('progress_percentage');
            }
        });

        // 7. Mettre à jour les données existantes pour les types d'étapes
        DB::table('workflow_steps')
            ->whereNotIn('step_type', ['manual', 'automatic', 'approval'])
            ->update(['step_type' => 'manual']);
    }

    /**
     * Reverse the migrations.
     * Cette méthode ne restaure pas complètement l'état précédent car des données pourraient être perdues.
     */
    public function down(): void
    {
        // Restaurer la structure de workflow_templates
        Schema::table('workflow_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('workflow_templates', 'category')) {
                $table->string('category', 50)->default('mail_processing');
                $table->json('configuration')->nullable()->comment('Configuration du workflow');
            }
        });

        // Restaurer la structure de workflow_steps
        Schema::table('workflow_steps', function (Blueprint $table) {
            if (Schema::hasColumn('workflow_steps', 'estimated_hours') && !Schema::hasColumn('workflow_steps', 'estimated_duration')) {
                $table->renameColumn('estimated_hours', 'estimated_duration');
            }

            // Restaurer le type enum
            $table->enum('step_type', ['manual', 'automatic', 'approval', 'notification', 'conditional'])->change();

            if (!Schema::hasColumn('workflow_steps', 'configuration')) {
                $table->json('configuration')->nullable()->comment('Configuration de l\'étape');
                $table->boolean('can_be_skipped')->default(false);
                $table->json('conditions')->nullable()->comment('Conditions pour cette étape');
            }
        });

        // Restaurer la structure de workflow_step_assignments
        Schema::table('workflow_step_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('workflow_step_assignments', 'assignee_type')) {
                $table->enum('assignee_type', ['user', 'organisation', 'role', 'department', 'auto'])->default('user');
                $table->foreignId('assignee_role_id')->nullable()->comment('Role assigné (si vous avez une table roles)');
                $table->json('assignment_rules')->nullable()->comment('Règles d\'assignation automatique');
                $table->boolean('allow_reassignment')->default(true)->comment('Permet la réassignation');
            }
        });

        // Restaurer la structure de workflow_instances
        Schema::table('workflow_instances', function (Blueprint $table) {
            if (!Schema::hasColumn('workflow_instances', 'context_data')) {
                $table->json('context_data')->nullable()->comment('Données contextuelles du workflow');
                $table->text('notes')->nullable();
            }
        });

        // Restaurer la structure de workflow_step_instances
        Schema::table('workflow_step_instances', function (Blueprint $table) {
            if (!Schema::hasColumn('workflow_step_instances', 'assignment_type')) {
                $table->enum('assignment_type', ['organisation', 'user', 'both'])->default('user')->comment('Type d\'assignation de l\'étape');
                $table->json('input_data')->nullable();
                $table->json('output_data')->nullable();
                $table->text('notes')->nullable();
                $table->text('assignment_notes')->nullable()->comment('Notes sur l\'assignation de l\'étape');
            }
        });

        // Restaurer la structure de tasks
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'assignment_type')) {
                $table->enum('assignment_type', ['organisation', 'user', 'both'])->default('user')->comment('Type d\'assignation');
                $table->json('tags')->nullable();
                $table->json('custom_fields')->nullable();
                $table->text('assignment_notes')->nullable()->comment('Notes sur l\'assignation');
                $table->integer('progress_percentage')->default(0);
            }
        });
    }
};
