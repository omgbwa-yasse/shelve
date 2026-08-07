<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Un risque peut être rattaché à une tâche précise du projet (optionnel) —
 * même patron que `objectives.task_id`/`kpis.task_id`
 * (2026_08_05_140000_add_task_to_objectives_and_kpis.php) : création depuis
 * la fiche tâche, jamais en singleton.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_risks', function (Blueprint $table) {
            $table->foreignId('task_id')->nullable()->after('project_id')->constrained('tasks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('project_risks', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
            $table->dropColumn('task_id');
        });
    }
};
