<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mail_containers', function (Blueprint $table) {
            // Supprimer la contrainte de clé étrangère existante avec type_id
            $table->dropForeign(['type_id']);

            // Ajouter la nouvelle colonne property_id avec contrainte de clé étrangère
            $table->foreignId('property_id')->nullable()->constrained('container_properties')->cascadeOnDelete();

            // Supprimer l'ancienne colonne type_id
            $table->dropColumn('type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mail_containers', function (Blueprint $table) {
            // Supprimer la contrainte de clé étrangère avec property_id
            $table->dropForeign(['property_id']);

            // Ajouter de nouveau la colonne type_id avec contrainte de clé étrangère
            $table->foreignId('type_id')->nullable()->constrained('container_types')->cascadeOnDelete();

            // Supprimer la colonne property_id
            $table->dropColumn('property_id');
        });
    }
};
