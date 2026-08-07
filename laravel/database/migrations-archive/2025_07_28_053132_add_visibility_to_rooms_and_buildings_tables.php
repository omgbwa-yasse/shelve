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
        // Ajouter le champ visibility à la table buildings
        Schema::table('buildings', function (Blueprint $table) {
            $table->enum('visibility', ['public', 'private', 'inherit'])
                  ->default('private')
                  ->after('description')
                  ->comment('Visibilité du bâtiment: public, private, ou inherit');
        });

        // Ajouter le champ visibility à la table rooms
        Schema::table('rooms', function (Blueprint $table) {
            $table->enum('visibility', ['public', 'private', 'inherit'])
                  ->default('inherit')
                  ->after('description')
                  ->comment('Visibilité de la salle: public, private, ou inherit du bâtiment parent');
        });

        // Supprimer la contrainte de clé étrangère type_id dans rooms
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropForeign(['type_id']);
            $table->dropColumn('type_id');
        });

        // Ajouter la colonne type enum directement dans rooms
        Schema::table('rooms', function (Blueprint $table) {
            $table->enum('type', ['archives', 'producer'])
                  ->default('archives')
                  ->after('visibility')
                  ->comment('Type de salle: archives ou producer');
        });

        // Supprimer la table room_types qui n'est plus nécessaire
        Schema::dropIfExists('room_types');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recréer la table room_types
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->enum('name', ['archives', 'producer']);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Supprimer la colonne type enum de rooms
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        // Rétablir la colonne type_id et sa contrainte
        Schema::table('rooms', function (Blueprint $table) {
            $table->unsignedBigInteger('type_id')->after('creator_id');
            $table->foreign('type_id')->references('id')->on('room_types')->onDelete('cascade');
        });

        // Supprimer le champ visibility de la table rooms
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('visibility');
        });

        // Supprimer le champ visibility de la table buildings
        Schema::table('buildings', function (Blueprint $table) {
            $table->dropColumn('visibility');
        });
    }
};
