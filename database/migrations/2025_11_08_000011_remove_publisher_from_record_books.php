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
        Schema::table('record_books', function (Blueprint $table) {
            // Retirer l'index sur publisher
            $table->dropIndex(['publisher']);

            // Retirer les anciennes colonnes
            $table->dropColumn(['publisher', 'series', 'series_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('record_books', function (Blueprint $table) {
            // Restaurer les anciennes colonnes
            $table->string('publisher')->nullable()->comment('Éditeur');
            $table->string('series')->nullable()->comment('Nom de la collection/série');
            $table->integer('series_number')->nullable()->comment('Numéro dans la série');

            // Restaurer l'index
            $table->index('publisher');
        });
    }
};
