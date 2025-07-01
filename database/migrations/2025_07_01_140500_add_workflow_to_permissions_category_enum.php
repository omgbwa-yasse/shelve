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
        // Ajouter la valeur 'workflow' à l'enum de la colonne 'category' de la table 'permissions'
        if (Schema::hasTable('permissions')) {
            DB::statement("ALTER TABLE permissions MODIFY COLUMN category ENUM('dashboard','mail','records','communications','reservations','transfers','deposits','users','settings','system','reports','tools','ai','backups','search','thesaurus','organizations','workflow') NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Retirer la valeur 'workflow' de l'enum de la colonne 'category' de la table 'permissions'
        if (Schema::hasTable('permissions')) {
            DB::statement("ALTER TABLE permissions MODIFY COLUMN category ENUM('dashboard','mail','records','communications','reservations','transfers','deposits','users','settings','system','reports','tools','ai','backups','search','thesaurus','organizations') NULL");
        }
    }
};
