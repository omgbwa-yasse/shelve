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
        // Supprimer la table si elle existe (pour s'assurer qu'on part d'une base propre)
        Schema::dropIfExists('task_users');

        // Créer la table task_users
        Schema::create('task_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['task_id', 'user_id'], 'task_user_unique');
        });

        // Insérer les données depuis task_assignments
        DB::statement("
            INSERT INTO task_users (task_id, user_id, created_at, updated_at)
            SELECT task_id, assignee_user_id, NOW(), NOW()
            FROM task_assignments
            WHERE assignee_type = 'user'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_users');
    }
};
