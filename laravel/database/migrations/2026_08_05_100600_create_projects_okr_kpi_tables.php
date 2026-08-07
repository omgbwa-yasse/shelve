<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module Projet / Tâche / OKR / KPI — voir `evolution/PROJECT-OKR-KPI-PLAN.md`.
 *
 * - projects          : rattachable à un Workplace, une Organisation (unité
 *                       administrative) ou un User (personne) via `attachable`
 *                       (même patron que `Task.taskable`, déjà en place — aucune
 *                       migration n'est nécessaire sur `tasks` pour que
 *                       `taskable_type = Project::class` fonctionne).
 * - objectives        : le "O" d'OKR, rattachable directement OU via un projet
 *                       (`project_id` nullable — un OKR d'équipe/personne n'a pas
 *                       toujours de projet formel).
 * - key_results       : le "KR" d'OKR, toujours enfant d'un objectif.
 * - kpis              : indicateur suivi dans le temps, rattachable comme les projets.
 * - kpi_measurements  : historique des mesures d'un KPI (nécessaire pour une
 *                       tendance — un KPI sans historique perd sa dimension temporelle).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 190);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft'); // draft|active|on_hold|completed|archived
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->morphs('attachable'); // Workplace | Organisation | User
            $table->foreignId('organisation_id')->constrained('organisations')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organisation_id', 'status']);
        });

        Schema::create('objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->cascadeOnDelete();
            $table->string('title', 190);
            $table->text('description')->nullable();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->string('status', 20)->default('on_track'); // on_track|at_risk|off_track|done
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->morphs('attachable');
            $table->foreignId('organisation_id')->constrained('organisations')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organisation_id', 'status']);
        });

        Schema::create('key_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objective_id')->constrained('objectives')->cascadeOnDelete();
            $table->string('title', 190);
            $table->string('metric_type', 20)->default('number'); // number|percentage|currency|boolean
            $table->decimal('start_value', 15, 2)->default(0);
            $table->decimal('target_value', 15, 2);
            $table->decimal('current_value', 15, 2)->default(0);
            $table->string('unit', 30)->nullable();
            $table->string('status', 20)->default('on_track'); // on_track|at_risk|off_track|done
            $table->date('due_date')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('objective_id');
        });

        Schema::create('kpis', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 190);
            $table->text('description')->nullable();
            $table->string('unit', 30)->nullable();
            $table->decimal('target_value', 15, 2)->nullable();
            $table->string('direction', 20)->default('higher_is_better'); // higher_is_better|lower_is_better
            $table->string('frequency', 20)->default('monthly'); // daily|weekly|monthly|quarterly|yearly
            $table->morphs('attachable');
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('organisation_id')->constrained('organisations')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('organisation_id');
        });

        Schema::create('kpi_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_id')->constrained('kpis')->cascadeOnDelete();
            $table->decimal('value', 15, 2);
            $table->date('measured_at');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['kpi_id', 'measured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_measurements');
        Schema::dropIfExists('kpis');
        Schema::dropIfExists('key_results');
        Schema::dropIfExists('objectives');
        Schema::dropIfExists('projects');
    }
};
