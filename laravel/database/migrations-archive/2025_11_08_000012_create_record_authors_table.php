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
        // Table des auteurs normalisée (format UNIMARC)
        Schema::create('record_authors', function (Blueprint $table) {
            $table->id();

            // Type d'auteur
            $table->enum('author_type', ['person', 'organization', 'conference'])->default('person')->comment('Author type');

            // Identification
            $table->string('last_name')->comment('Last name or organization name');
            $table->string('first_name')->nullable()->comment('First name');
            $table->string('full_name')->comment('Full name (indexed)');
            $table->string('pseudonym')->nullable()->comment('Pseudonym or pen name');
            $table->text('rejected_form')->nullable()->comment('Other name forms');

            // Biographical dates
            $table->string('dates', 100)->nullable()->comment('Dates (ex: 1909-1943)');
            $table->integer('birth_year')->nullable()->comment('Birth year');
            $table->integer('death_year')->nullable()->comment('Death year');
            $table->string('birth_place')->nullable()->comment('Birth place');
            $table->string('country', 100)->nullable()->comment('Country');
            $table->string('nationality')->nullable()->comment('Nationality (ISO 3166-1 alpha-2)');
            $table->string('language', 50)->nullable()->comment('Language');

            // Professional information
            $table->text('biographical_note')->nullable()->comment('Biographical note');
            $table->text('biography')->nullable()->comment('Short biography (legacy)');
            $table->json('specializations')->nullable()->comment('Specializations/Fields (JSON array)');
            $table->string('website')->nullable()->comment('Personal website');
            $table->string('photo')->nullable()->comment('Author photo');

            // External identifiers
            $table->string('ppn_authority', 20)->nullable()->comment('PPN authority SUDOC');
            $table->string('isni', 50)->nullable()->unique()->comment('ISNI (International Standard Name Identifier)');
            $table->string('orcid')->nullable()->unique()->comment('ORCID iD');
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
            $table->index('author_type');
            $table->index('ppn_authority');
            $table->index('nationality');
            $table->index('status');
            $table->fullText(['full_name', 'pseudonym', 'biography', 'biographical_note'], 'authors_fulltext_idx');
        });

        // Table de responsabilité (relation many-to-many between authors and books)
        Schema::create('record_author_book', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')
                ->constrained('record_authors')
                ->onDelete('cascade')
                ->comment('Author (person, organization, or conference)');
            $table->foreignId('book_id')
                ->constrained('record_books')
                ->onDelete('cascade')
                ->comment('Book');

            $table->string('responsibility_type', 10)->nullable()->comment('UNIMARC code (ex: 070=author, 730=translator)');
            $table->string('function', 100)->nullable()->comment('Function (Author, Translator, Preface writer, etc.)');
            $table->string('role')->default('author')->comment('Role (legacy: author, editor, translator, illustrator, contributor)');
            $table->integer('display_order')->default(1)->comment('Display order');
            $table->text('notes')->nullable()->comment('Notes on contribution');

            $table->timestamps();

            // Index
            $table->index('author_id');
            $table->index('book_id');
            $table->index('responsibility_type');
            $table->index('role');
            $table->index(['book_id', 'display_order']);
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
