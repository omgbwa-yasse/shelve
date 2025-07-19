<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Cette migration supprime la table record_term qui est devenue obsolète
     * car remplacée par la table record_thesaurus_concept qui offre une meilleure
     * structure de données avec des métadonnées supplémentaires (poids, contexte, etc.)
     */
    public function up(): void
    {
        // Suppression des contraintes de clé étrangère d'abord pour éviter les erreurs
        Schema::table('record_term', function (Blueprint $table) {
            $table->dropForeign(['record_id']);
            $table->dropForeign(['term_id']);
        });

        // Suppression de la table record_term
        Schema::dropIfExists('record_term');
    }

    /**
     * Reverse the migrations.
     *
     * Recréation de la table record_term et de ses relations
     */
    public function down(): void
    {
        Schema::create('record_term', function (Blueprint $table) {
            $table->unsignedBigInteger('record_id')->nullable(false);
            $table->unsignedInteger('term_id')->nullable(false);
            $table->timestamps();
            $table->primary(['record_id', 'term_id']);
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
        });
    }
};
