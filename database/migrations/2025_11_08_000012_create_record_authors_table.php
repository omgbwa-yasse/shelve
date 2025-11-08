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
        // Table des auteurs normalisée
        Schema::create('record_authors', function (Blueprint $table) {
            $table->id();

            // Identification
            $table->string('first_name')->nullable()->comment('Prénom');
            $table->string('last_name')->comment('Nom de famille');
            $table->string('full_name')->comment('Nom complet (indexé)');
            $table->string('pseudonym')->nullable()->comment('Pseudonyme ou nom de plume');

            // Dates biographiques
            $table->integer('birth_year')->nullable()->comment('Année de naissance');
            $table->integer('death_year')->nullable()->comment('Année de décès');
            $table->string('birth_place')->nullable()->comment('Lieu de naissance');
            $table->string('nationality')->nullable()->comment('Nationalité (ISO 3166-1 alpha-2)');

            // Informations professionnelles
            $table->text('biography')->nullable()->comment('Biographie courte');
            $table->json('specializations')->nullable()->comment('Spécialisations/Domaines (JSON array)');
            $table->string('website')->nullable()->comment('Site web personnel');
            $table->string('photo')->nullable()->comment('Photo de l\'auteur');

            // Identifiants externes
            $table->string('orcid')->nullable()->unique()->comment('ORCID iD');
            $table->string('isni')->nullable()->unique()->comment('ISNI (International Standard Name Identifier)');
            $table->string('viaf')->nullable()->comment('VIAF (Virtual International Authority File)');

            // Statistiques
            $table->integer('total_books')->default(0)->comment('Nombre total de livres');
            $table->integer('total_works')->default(0)->comment('Nombre total d\'œuvres (tous rôles)');

            // Métadonnées
            $table->json('metadata')->nullable()->comment('Métadonnées additionnelles');
            $table->enum('status', ['active', 'deceased', 'unknown'])->default('active')->comment('Statut');

            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('last_name');
            $table->index('full_name');
            $table->index('nationality');
            $table->index('status');
            $table->fullText(['full_name', 'pseudonym', 'biography']);
        });

        // Table pivot pour la relation many-to-many entre auteurs et livres
        Schema::create('record_author_book', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')
                ->constrained('record_authors')
                ->onDelete('cascade')
                ->comment('Auteur');
            $table->foreignId('book_id')
                ->constrained('record_books')
                ->onDelete('cascade')
                ->comment('Livre');

            $table->string('role')->default('author')->comment('Rôle (author, editor, translator, illustrator, contributor)');
            $table->integer('display_order')->default(0)->comment('Ordre d\'affichage');
            $table->text('notes')->nullable()->comment('Notes sur la contribution');

            $table->timestamps();

            // Index
            $table->index('author_id');
            $table->index('book_id');
            $table->index('role');
            $table->index(['book_id', 'display_order']);
            $table->unique(['author_id', 'book_id', 'role'], 'author_book_role_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_author_book');
        Schema::dropIfExists('record_authors');
    }
};
