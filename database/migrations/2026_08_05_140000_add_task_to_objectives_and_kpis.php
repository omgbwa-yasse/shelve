<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Les OKR et KPI sont rattachés aux tâches du projet (exigence utilisateur du
 * 2026-08-05) et non créés de manière singleton : ajout de `task_id` (nullable)
 * sur `objectives` et `kpis`. L'ajout se fait depuis la création d'une tâche
 * de projet (panneau « Créer une tâche » de la fiche projet).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('objectives', function (Blueprint $table) {
            if (!Schema::hasColumn('objectives', 'task_id')) {
                $table->unsignedBigInteger('task_id')->nullable()->after('project_id');
                $table->foreign('task_id')->references('id')->on('tasks')->nullOnDelete();
            }
        });

        Schema::table('kpis', function (Blueprint $table) {
            if (!Schema::hasColumn('kpis', 'task_id')) {
                $table->unsignedBigInteger('task_id')->nullable()->after('frequency');
                $table->foreign('task_id')->references('id')->on('tasks')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('objectives', function (Blueprint $table) {
            if (Schema::hasColumn('objectives', 'task_id')) {
                $table->dropForeign(['task_id']);
                $table->dropColumn('task_id');
            }
        });

        Schema::table('kpis', function (Blueprint $table) {
            if (Schema::hasColumn('kpis', 'task_id')) {
                $table->dropForeign(['task_id']);
                $table->dropColumn('task_id');
            }
        });
    }
};
