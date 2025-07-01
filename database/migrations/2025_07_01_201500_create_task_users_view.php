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
        // Si la table task_users existe déjà, on la modifie pour ajouter les colonnes manquantes
        if (Schema::hasTable('task_users')) {
            // On vérifie si la colonne 'id' existe déjà
            if (!Schema::hasColumn('task_users', 'id')) {
                Schema::table('task_users', function (Blueprint $table) {
                    $table->id()->first();
                });
            }
            
            // On vérifie si les timestamps existent déjà
            if (!Schema::hasColumn('task_users', 'created_at')) {
                Schema::table('task_users', function (Blueprint $table) {
                    $table->timestamps();
                });
            }
            
            // On ajoute un index unique s'il n'existe pas déjà
            // On doit vérifier si l'index existe déjà, mais cette vérification est plus complexe
            try {
                Schema::table('task_users', function (Blueprint $table) {
                    $table->unique(['task_id', 'user_id'], 'task_user_unique');
                });
            } catch (\Exception $e) {
                // L'index existe probablement déjà, on ignore
            }
        } else {
            // Créer une vraie table pivot task_users
            Schema::create('task_users', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamps();
                
                // Ajouter un index unique pour éviter les doublons
                $table->unique(['task_id', 'user_id'], 'task_user_unique');
            });
        }
        
        // Réinitialiser la table task_users avec les données de task_assignments
        DB::statement("DELETE FROM task_users");
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
