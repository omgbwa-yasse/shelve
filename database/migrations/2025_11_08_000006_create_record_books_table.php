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
        Schema::create('record_books', function (Blueprint $table) {
            $table->id();

            // Identification bibliographique
            $table->string('isbn')->nullable()->unique()->comment('ISBN-10 ou ISBN-13');
            $table->string('title')->comment('Titre du livre');
            $table->string('subtitle')->nullable()->comment('Sous-titre');

            // Édition
            $table->string('publisher')->nullable()->comment('Éditeur');
            $table->integer('publication_year')->nullable()->comment('Année de publication');
            $table->string('edition')->nullable()->comment('Édition (1ère, 2ème, etc.)');
            $table->string('place_of_publication')->nullable()->comment('Lieu de publication');

            // Classification
            $table->string('dewey')->nullable()->comment('Classification Dewey');
            $table->string('lcc')->nullable()->comment('Library of Congress Classification');
            $table->json('subjects')->nullable()->comment('Sujets/Thèmes (JSON array)');

            // Description physique
            $table->integer('pages')->nullable()->comment('Nombre de pages');
            $table->string('format')->nullable()->comment('Format (in-8, in-4, A4, etc.)');
            $table->string('binding')->nullable()->comment('Reliure (broché, relié, etc.)');
            $table->string('language')->default('fr')->comment('Langue (ISO 639-1)');
            $table->string('dimensions')->nullable()->comment('Dimensions (HxLxP en cm)');

            // Contenu
            $table->text('description')->nullable()->comment('Résumé/Description');
            $table->text('table_of_contents')->nullable()->comment('Table des matières');
            $table->text('notes')->nullable()->comment('Notes diverses');

            // Collection/Série
            $table->string('series')->nullable()->comment('Nom de la collection/série');
            $table->integer('series_number')->nullable()->comment('Numéro dans la série');

            // Statistiques
            $table->integer('total_copies')->default(0)->comment('Nombre total d\'exemplaires');
            $table->integer('available_copies')->default(0)->comment('Exemplaires disponibles');
            $table->integer('loan_count')->default(0)->comment('Nombre total de prêts');
            $table->integer('reservation_count')->default(0)->comment('Nombre de réservations');

            // Métadonnées et configuration
            $table->json('metadata')->nullable()->comment('Métadonnées personnalisées');
            $table->enum('access_level', ['public', 'internal', 'confidential', 'secret'])
                ->default('public')
                ->comment('Niveau d\'accès');
            $table->enum('status', ['active', 'archived', 'withdrawn'])
                ->default('active')
                ->comment('Statut du livre');

            // Relations organisationnelles
            $table->foreignId('creator_id')->constrained('users')->comment('Créateur de la fiche');
            $table->foreignId('organisation_id')->constrained('organisations')->comment('Organisation propriétaire');

            // Dates et timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('isbn');
            $table->index('publication_year');
            $table->index('publisher');
            $table->index('dewey');
            $table->index('status');
            $table->index('creator_id');
            $table->index('organisation_id');
            $table->index(['organisation_id', 'status']);
            $table->fullText(['title', 'subtitle', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_books');
    }
};
