<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Étape 10 — Alignement Constellio : module Workflow.
 *
 * - workflow_transitions : règle d'assignation dynamique de la tâche cible
 *   (`assignment_type` : creator | previous | manager | function) + valeur
 *   (`assignment_value`) + échéance en jours ouvrables (`due_days`).
 * - workflow_definitions : sécurité de démarrage (`visibility` public/private,
 *   utilisateurs et rôles autorisés).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_transitions', function (Blueprint $table) {
            if (! Schema::hasColumn('workflow_transitions', 'assignment_type')) {
                $table->string('assignment_type', 30)->nullable()->after('condition');
            }
            if (! Schema::hasColumn('workflow_transitions', 'assignment_value')) {
                $table->string('assignment_value', 100)->nullable()->after('assignment_type');
            }
            if (! Schema::hasColumn('workflow_transitions', 'due_days')) {
                $table->unsignedInteger('due_days')->nullable()->after('assignment_value');
            }
        });

        Schema::table('workflow_definitions', function (Blueprint $table) {
            if (! Schema::hasColumn('workflow_definitions', 'visibility')) {
                $table->string('visibility', 20)->default('public')->after('status');
            }
            if (! Schema::hasColumn('workflow_definitions', 'allowed_user_ids')) {
                $table->json('allowed_user_ids')->nullable()->after('visibility');
            }
            if (! Schema::hasColumn('workflow_definitions', 'allowed_role_ids')) {
                $table->json('allowed_role_ids')->nullable()->after('allowed_user_ids');
            }
        });
    }

    public function down(): void
    {
        Schema::table('workflow_transitions', function (Blueprint $table) {
            foreach (['assignment_type', 'assignment_value', 'due_days'] as $column) {
                if (Schema::hasColumn('workflow_transitions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('workflow_definitions', function (Blueprint $table) {
            foreach (['visibility', 'allowed_user_ids', 'allowed_role_ids'] as $column) {
                if (Schema::hasColumn('workflow_definitions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
