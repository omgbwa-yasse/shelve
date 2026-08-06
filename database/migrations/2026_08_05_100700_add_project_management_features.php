<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extension du module Projet pour rivaliser avec les fonctionnalités de base
 * de MS Project — voir `evolution/PROJECT-OKR-KPI-PLAN.md` (chantier initial)
 * et le constat du 2026-08-05 : jalons, livrables, ressources (humaines,
 * financières, matérielles), dépendances entre tâches et rapports d'étape
 * étaient absents.
 *
 * Choix délibérés :
 * - Pas de table d'alertes persistante : les alertes (jalon en retard, tâche
 *   en retard, budget dépassé, livrable en retard) sont calculées à la volée
 *   (voir ProjectAlertService) — éviter la dépendance à un job planifié
 *   fiable dans cet environnement, et éviter la désynchronisation d'un état
 *   persistant recalculable.
 * - `project_resources` est une table unique à discriminant `type`
 *   (human|financial|material|informational) plutôt que 4 tables séparées —
 *   assez pour couvrir les cas d'usage sans complexifier le modèle.
 * - Les tables enfants n'ont pas leur propre `organisation_id` : l'autorisation
 *   se fait via la policy du `Project` parent (même patron que `key_results`
 *   vis-à-vis d'`Objective`).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('budget_planned', 15, 2)->nullable()->after('end_date');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('due_date');
            $table->unsignedTinyInteger('percent_complete')->default(0)->after('start_date');
            $table->decimal('estimated_hours', 8, 2)->nullable()->after('percent_complete');
            $table->decimal('actual_hours', 8, 2)->nullable()->after('estimated_hours');
        });

        Schema::create('project_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name', 190);
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status', 20)->default('pending'); // pending|reached|missed
            $table->timestamp('reached_at')->nullable();
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });

        Schema::create('project_deliverables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained('project_milestones')->nullOnDelete();
            $table->string('name', 190);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft'); // draft|submitted|approved|rejected
            $table->date('due_date')->nullable();
            // Rattachement à un fichier existant (module Attachments) — pas de nouvel
            // uploader : un livrable référence une pièce jointe déjà déposée ailleurs,
            // ou reste purement descriptif si aucun fichier n'est encore prêt.
            $table->foreignId('attachment_id')->nullable()->constrained('attachments')->nullOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });

        Schema::create('project_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('type', 20); // human|financial|material|informational
            $table->string('name', 190);
            // Ressource humaine
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('role', 100)->nullable();
            $table->decimal('allocation_percent', 5, 2)->nullable(); // % du temps de la personne
            $table->decimal('unit_cost', 12, 2)->nullable(); // coût horaire/journalier/unitaire
            // Financier / matériel
            $table->decimal('quantity', 12, 2)->nullable();
            $table->decimal('planned_amount', 15, 2)->nullable();
            $table->decimal('actual_amount', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['project_id', 'type']);
        });

        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('predecessor_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('successor_id')->constrained('tasks')->cascadeOnDelete();
            $table->string('type', 20)->default('finish_to_start'); // finish_to_start|start_to_start|finish_to_finish|start_to_finish
            $table->integer('lag_days')->default(0);
            $table->timestamps();

            $table->unique(['predecessor_id', 'successor_id']);
        });

        Schema::create('project_status_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->date('reported_at');
            $table->unsignedTinyInteger('percent_complete')->default(0);
            $table->text('summary');
            $table->text('risks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['project_id', 'reported_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_status_reports');
        Schema::dropIfExists('task_dependencies');
        Schema::dropIfExists('project_resources');
        Schema::dropIfExists('project_deliverables');
        Schema::dropIfExists('project_milestones');

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'percent_complete', 'estimated_hours', 'actual_hours']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('budget_planned');
        });
    }
};
