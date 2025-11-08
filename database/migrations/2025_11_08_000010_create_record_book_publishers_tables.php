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
        // Table des éditeurs
        Schema::create('record_book_publishers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Nom de l\'éditeur');
            $table->string('original_name')->nullable()->comment('Nom original (si différent)');
            $table->string('country')->nullable()->comment('Pays d\'origine (ISO 3166-1 alpha-2)');
            $table->string('city')->nullable()->comment('Ville principale');
            $table->integer('founded_year')->nullable()->comment('Année de fondation');
            $table->integer('ceased_year')->nullable()->comment('Année de cessation d\'activité');
            $table->text('description')->nullable()->comment('Description de l\'éditeur');
            $table->string('website')->nullable()->comment('Site web');
            $table->string('logo')->nullable()->comment('Logo de l\'éditeur');
            $table->enum('status', ['active', 'inactive', 'acquired', 'ceased'])->default('active')->comment('Statut');
            $table->json('metadata')->nullable()->comment('Métadonnées additionnelles');
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('name');
            $table->index('country');
            $table->index('status');
            $table->fullText(['name', 'original_name', 'description']);
        });

        // Table des collections/séries d'éditeurs
        Schema::create('record_book_publisher_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publisher_id')
                ->constrained('record_book_publishers')
                ->onDelete('cascade')
                ->comment('Éditeur de la collection');
            $table->string('name')->comment('Nom de la collection/série');
            $table->text('description')->nullable()->comment('Description de la collection');
            $table->string('issn')->nullable()->unique()->comment('ISSN de la collection (si applicable)');
            $table->integer('started_year')->nullable()->comment('Année de début');
            $table->integer('ended_year')->nullable()->comment('Année de fin (si terminée)');
            $table->string('editor')->nullable()->comment('Directeur/Éditeur de la collection');
            $table->json('subjects')->nullable()->comment('Thématiques de la collection');
            $table->integer('total_volumes')->default(0)->comment('Nombre total de volumes');
            $table->enum('status', ['active', 'completed', 'discontinued'])->default('active')->comment('Statut');
            $table->json('metadata')->nullable()->comment('Métadonnées additionnelles');
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('publisher_id');
            $table->index('name');
            $table->index('issn');
            $table->index('status');
            $table->index(['publisher_id', 'status']);
            $table->fullText(['name', 'description']);
        });

        // Ajouter les colonnes de relation à la table record_books
        Schema::table('record_books', function (Blueprint $table) {
            // Ajouter les foreign keys
            $table->foreignId('publisher_id')
                ->nullable()
                ->after('subtitle')
                ->constrained('record_book_publishers')
                ->onDelete('set null')
                ->comment('Éditeur du livre');

            $table->foreignId('series_id')
                ->nullable()
                ->after('notes')
                ->constrained('record_book_publisher_series')
                ->onDelete('set null')
                ->comment('Collection/Série');

            // Conserver temporairement les anciennes colonnes pour migration des données
            // On les retirera dans une migration ultérieure après migration des données
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Retirer les foreign keys de record_books
        Schema::table('record_books', function (Blueprint $table) {
            $table->dropForeign(['publisher_id']);
            $table->dropForeign(['series_id']);
            $table->dropColumn(['publisher_id', 'series_id']);
        });

        // Supprimer les tables
        Schema::dropIfExists('record_book_publisher_series');
        Schema::dropIfExists('record_book_publishers');
    }
};
