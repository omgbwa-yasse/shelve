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
        // Sous SQLite, la colonne mail_containers.type_id n'a pas pu être supprimée
        // par la migration précédente (SQLite refuse de retirer une colonne portant
        // une contrainte FK) : elle référence toujours container_types. Supprimer la
        // table ferait échouer toute insertion dans mail_containers avec
        // « no such table: main.container_types ». On laisse donc la table vide en place
        // sur SQLite (dev/démo) ; elle est bien supprimée sur MySQL (production).
        if (DB::getDriverName() === 'sqlite'
            && Schema::hasTable('mail_containers')
            && Schema::hasColumn('mail_containers', 'type_id')) {
            return;
        }

        Schema::dropIfExists('container_types');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('container_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false);
            $table->string('description', 100)->nullable();
            $table->timestamps();
        });
    }
};
