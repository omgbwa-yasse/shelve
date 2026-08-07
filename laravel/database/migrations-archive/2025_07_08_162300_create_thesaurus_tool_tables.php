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
        // Table des schémas de thésaurus
        Schema::create('thesaurus_schemes', function (Blueprint $table) {
            $table->id();
            $table->string('uri', 191)->unique()->comment('URI unique du schéma SKOS');
            $table->string('identifier', 191)->nullable()->comment('Identifiant externe');
            $table->string('title', 500)->nullable()->comment('Titre principal du schéma');
            $table->text('description')->nullable()->comment('Description du schéma');
            $table->string('language', 10)->default('fr-fr')->comment('Langue par défaut');
            $table->timestamps();
            $table->index('uri');
        });

        // Table des concepts
        Schema::create('thesaurus_concepts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheme_id')
                  ->constrained('thesaurus_schemes')
                  ->onDelete('cascade');
            $table->string('uri', 191)->unique()->comment('URI unique du concept SKOS');
            $table->string('notation', 100)->nullable()->comment('Notation du concept');
            $table->tinyInteger('status')->default(1)->comment('Statut du concept');
            $table->timestamps();
            $table->index('notation');
        });

        // Table des labels
        Schema::create('thesaurus_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('concept_id')
                  ->constrained('thesaurus_concepts')
                  ->onDelete('cascade');
            $table->string('uri', 191)->nullable()->comment('URI du label');
            $table->enum('type', ['prefLabel', 'altLabel', 'hiddenLabel'])->default('prefLabel');
            $table->string('literal_form', 500)->comment('Forme littérale du label');
            $table->string('language', 10)->default('fr-fr')->comment('Langue du label');
            $table->timestamps();
            $table->index(['concept_id', 'type']);
        });

        // Table des notes de concepts
        Schema::create('thesaurus_concept_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('concept_id')
                  ->constrained('thesaurus_concepts')
                  ->onDelete('cascade');
            $table->enum('type', ['definition', 'scopeNote', 'example', 'historyNote', 'editorialNote'])
                  ->default('definition');
            $table->text('note')->comment('Contenu de la note');
            $table->string('language', 10)->default('fr-fr')->comment('Langue de la note');
            $table->timestamps();
            $table->index(['concept_id', 'type']);
        });

        // Table des relations entre concepts
        Schema::create('thesaurus_concept_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('concept_id')
                  ->constrained('thesaurus_concepts')
                  ->onDelete('cascade');
            $table->foreignId('related_concept_id')
                  ->constrained('thesaurus_concepts')
                  ->onDelete('cascade');
            $table->enum('relation_type', ['broader', 'narrower', 'related', 'broadMatch', 'narrowMatch', 'exactMatch', 'relatedMatch'])
                  ->comment('Type de relation SKOS');
            $table->timestamps();
            $table->index(['concept_id', 'relation_type']);
            $table->unique(['concept_id', 'related_concept_id', 'relation_type'], 'thesaurus_concept_relations_unique');
        });

        // Table des propriétés de concepts
        Schema::create('thesaurus_concept_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('concept_id')
                  ->constrained('thesaurus_concepts')
                  ->onDelete('cascade');
            $table->string('property_name', 100)->comment('Nom de la propriété');
            $table->text('property_value')->comment('Valeur de la propriété');
            $table->string('language', 10)->nullable()->comment('Langue de la propriété');
            $table->timestamps();
            $table->index(['concept_id', 'property_name']);
        });

        // Table des organisations
        Schema::create('thesaurus_organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 500)->comment('Nom de l\'organisation');
            $table->string('homepage', 191)->nullable()->comment('Site web');
            $table->string('email', 255)->nullable()->comment('Email');
            $table->timestamps();
        });

        // Table des namespaces
        Schema::create('thesaurus_namespaces', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 50)->unique()->comment('Préfixe du namespace');
            $table->string('namespace_uri', 191)->comment('URI du namespace');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thesaurus_concept_properties');
        Schema::dropIfExists('thesaurus_concept_relations');
        Schema::dropIfExists('thesaurus_concept_notes');
        Schema::dropIfExists('thesaurus_labels');
        Schema::dropIfExists('thesaurus_concepts');
        Schema::dropIfExists('thesaurus_schemes');
        Schema::dropIfExists('thesaurus_organizations');
        Schema::dropIfExists('thesaurus_namespaces');
    }
};
