<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Créer une table intermédiaire task_users
        Schema::create('task_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            
            // Index et contrainte d'unicité
            $table->unique(['task_id', 'user_id']);
        });
        
        // Remplir la table task_users avec les données existantes de task_assignments
        DB::statement('
            INSERT INTO task_users (task_id, user_id, created_at, updated_at)
            SELECT task_id, assignee_user_id, NOW(), NOW()
            FROM task_assignments
            WHERE assignee_type = "user"
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_users');
    }
};
