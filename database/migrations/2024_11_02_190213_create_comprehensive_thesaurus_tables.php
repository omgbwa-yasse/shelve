<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createCoreTables();
        $this->createLabelTables();
        $this->createRelationTables();
        $this->createCollectionTables();
        $this->createSupportTables();
    }

    private function createCoreTables(): void
    {
        // Table des schémas de concepts (thésaurus multiples)
        Schema::create('concept_schemes', function (Blueprint $table) {
            $table->id();
            $table->text('uri'); // URI SKOS du schéma (changé en text)
            $table->string('uri_hash', 64)->unique(); // Hash MD5 de l'URI pour l'unicité
            $table->string('identifier', 100)->unique(); // Identifiant court (ex: T3, GEO, THEM)
            $table->string('title', 200); // dc:title
            $table->text('description')->nullable(); // dc:description
            $table->string('creator', 200)->nullable(); // dc:creator
            $table->string('contributor', 200)->nullable(); // dc:contributor
            $table->string('publisher', 200)->nullable(); // dc:publisher
            $table->string('type', 100)->nullable(); // dc:type
            $table->string('rights', 200)->nullable(); // dc:rights
            $table->string('subject', 200)->nullable(); // dc:subject
            $table->string('coverage', 200)->nullable(); // dc:coverage
            $table->string('language', 10)->default('fr'); // dc:language
            $table->enum('status', ['active', 'deprecated', 'draft'])->default('active');
            $table->json('metadata')->nullable(); // Métadonnées additionnelles
            $table->timestamps();

            $table->index(['status', 'language']);
        });

        // Table principale des concepts
        Schema::create('concepts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('concept_scheme_id'); // Référence au thésaurus
            $table->text('uri'); // URI SKOS du concept (changé en text)
            $table->string('uri_hash', 64)->unique(); // Hash MD5 de l'URI pour l'unicité
            $table->string('notation', 50)->nullable(); // Code/notation (ex: T3-139)
            $table->string('preferred_label', 200); // skos:prefLabel
            $table->string('language', 10)->default('fr'); // xml:lang
            $table->text('definition')->nullable(); // skos:definition
            $table->text('scope_note')->nullable(); // skos:scopeNote
            $table->text('history_note')->nullable(); // skos:historyNote
            $table->text('editorial_note')->nullable(); // skos:editorialNote
            $table->text('example')->nullable(); // skos:example
            $table->text('change_note')->nullable(); // skos:changeNote
            $table->enum('status', ['approved', 'candidate', 'deprecated', 'withdrawn'])->default('candidate');
            $table->integer('iso_status')->nullable(); // iso-thes:status
            $table->boolean('is_top_concept')->default(false); // skos:hasTopConcept
            $table->string('category', 100)->nullable(); // Domaine/facette
            $table->timestamp('date_created')->nullable(); // dct:created
            $table->timestamp('date_modified')->nullable(); // dct:modified
            $table->json('additional_properties')->nullable(); // Propriétés personnalisées
            $table->timestamps();

            $table->foreign('concept_scheme_id')->references('id')->on('concept_schemes')->onDelete('cascade');
            $table->index(['concept_scheme_id', 'status']);
            $table->index(['language', 'status']);
            $table->index('is_top_concept');
            $table->index('notation');
        });
    }

    private function createLabelTables(): void
    {
        // Table des libellés étendus SKOS-XL
        Schema::create('xl_labels', function (Blueprint $table) {
            $table->id();
            $table->text('uri'); // URI du libellé
            $table->string('uri_hash', 64)->unique(); // Hash SHA-256 de l'URI pour l'unicité
            $table->unsignedBigInteger('concept_id'); // Concept associé
            $table->enum('label_type', ['prefLabel', 'altLabel', 'hiddenLabel']); // Type de libellé SKOS-XL
            $table->string('literal_form', 200); // xl:literalForm
            $table->string('language', 10)->default('fr'); // xml:lang
            $table->timestamps();

            $table->foreign('concept_id')->references('id')->on('concepts')->onDelete('cascade');
            $table->index(['concept_id', 'label_type']);
            $table->index('language');
        });

        // Table des libellés alternatifs simples (pour compatibilité)
        Schema::create('alternative_labels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('concept_id');
            $table->string('label', 200); // skos:altLabel ou skos:hiddenLabel
            $table->enum('label_type', ['altLabel', 'hiddenLabel'])->default('altLabel');
            $table->string('language', 10)->default('fr');
            $table->enum('relation_type', [
                'synonym', 'quasi_synonym', 'abbreviation', 'acronym',
                'scientific_name', 'common_name', 'brand_name', 'variant_spelling',
                'old_form', 'modern_form', 'antonym', 'broader_synonym', 'narrower_synonym'
            ])->default('synonym');
            $table->timestamps();

            $table->foreign('concept_id')->references('id')->on('concepts')->onDelete('cascade');
            $table->index(['concept_id', 'label_type']);
        });
    }

    private function createRelationTables(): void
    {
        // Relations hiérarchiques (skos:broader/narrower)
        Schema::create('hierarchical_relations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('broader_concept_id'); // Concept générique
            $table->unsignedBigInteger('narrower_concept_id'); // Concept spécifique
            $table->enum('relation_type', [
                'generic', 'partitive', 'instance', 'disciplinary', 'causal'
            ])->default('generic');
            $table->string('relation_uri', 500)->nullable(); // URI de la relation si définie
            $table->timestamps();

            $table->foreign('broader_concept_id')->references('id')->on('concepts')->onDelete('cascade');
            $table->foreign('narrower_concept_id')->references('id')->on('concepts')->onDelete('cascade');
            $table->unique(['broader_concept_id', 'narrower_concept_id'], 'unique_hierarchical_relation');
        });

        // Relations associatives (skos:related)
        Schema::create('associative_relations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('concept1_id');
            $table->unsignedBigInteger('concept2_id');
            $table->enum('relation_subtype', [
                'cause_effect', 'whole_part', 'action_agent', 'action_product',
                'action_object', 'action_location', 'science_object', 'object_property',
                'object_role', 'raw_material_product', 'process_neutralizer',
                'object_origin', 'concept_measurement', 'profession_person',
                'temporal', 'spatial', 'functional', 'general'
            ])->default('general');
            $table->string('relation_uri', 500)->nullable(); // URI spécifique (ex: ginco:TermeAssocie)
            $table->text('relation_note')->nullable(); // Note sur la relation
            $table->timestamps();

            $table->foreign('concept1_id')->references('id')->on('concepts')->onDelete('cascade');
            $table->foreign('concept2_id')->references('id')->on('concepts')->onDelete('cascade');
            $table->unique(['concept1_id', 'concept2_id'], 'unique_associative_relation');
        });

        // Relations de mapping inter-thésaurus (skos:exactMatch, closeMatch, etc.)
        Schema::create('mapping_relations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_concept_id');
            $table->unsignedBigInteger('target_concept_id')->nullable(); // Peut être null si externe
            $table->string('target_uri', 500)->nullable(); // URI externe si pas dans la base
            $table->string('target_label', 200)->nullable(); // Libellé du concept cible
            $table->enum('mapping_type', [
                'exactMatch', 'closeMatch', 'broadMatch', 'narrowMatch', 'relatedMatch'
            ]);
            $table->string('target_scheme', 200)->nullable(); // Nom du vocabulaire cible
            $table->text('mapping_note')->nullable(); // Note sur le mapping
            $table->timestamps();

            $table->foreign('source_concept_id')->references('id')->on('concepts')->onDelete('cascade');
            $table->foreign('target_concept_id')->references('id')->on('concepts')->onDelete('cascade');
            $table->index(['source_concept_id', 'mapping_type']);
        });

        // Traductions entre langues
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_concept_id');
            $table->unsignedBigInteger('target_concept_id');
            $table->enum('translation_type', ['exact', 'partial', 'broad', 'narrow'])->default('exact');
            $table->text('translation_note')->nullable();
            $table->timestamps();

            $table->foreign('source_concept_id')->references('id')->on('concepts')->onDelete('cascade');
            $table->foreign('target_concept_id')->references('id')->on('concepts')->onDelete('cascade');
            $table->unique(['source_concept_id', 'target_concept_id'], 'unique_translation');
        });

        // Alignements avec référentiels externes
        Schema::create('external_alignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('concept_id');
            $table->string('external_uri', 500); // URI du concept externe
            $table->string('external_label', 200)->nullable(); // Libellé du concept externe
            $table->string('external_notation', 100)->nullable(); // Code du concept externe
            $table->string('external_vocabulary', 100); // Nom du vocabulaire externe
            $table->string('vocabulary_uri', 500)->nullable(); // URI du vocabulaire externe
            $table->enum('match_type', ['exact', 'close', 'broad', 'narrow', 'related'])->default('exact');
            $table->decimal('confidence_score', 3, 2)->nullable(); // Score de confiance 0-1
            $table->json('additional_metadata')->nullable(); // Métadonnées additionnelles
            $table->timestamps();

            $table->foreign('concept_id')->references('id')->on('concepts')->onDelete('cascade');
            $table->index(['external_vocabulary', 'match_type']);
            // Note: pas d'index sur external_uri car trop long pour MySQL
        });
    }

    private function createCollectionTables(): void
    {
        // Collections SKOS (pour regrouper des concepts)
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('concept_scheme_id');
            $table->text('uri');
            $table->string('uri_hash', 64)->unique(); // Hash SHA-256 de l'URI pour l'unicité
            $table->string('label', 200);
            $table->text('description')->nullable();
            $table->string('notation', 50)->nullable();
            $table->enum('collection_type', ['Collection', 'OrderedCollection'])->default('Collection');
            $table->timestamps();

            $table->foreign('concept_scheme_id')->references('id')->on('concept_schemes')->onDelete('cascade');
        });

        // Membres des collections
        Schema::create('collection_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('collection_id');
            $table->unsignedBigInteger('concept_id');
            $table->integer('order_index')->nullable(); // Pour OrderedCollection
            $table->timestamps();

            $table->foreign('collection_id')->references('id')->on('collections')->onDelete('cascade');
            $table->foreign('concept_id')->references('id')->on('concepts')->onDelete('cascade');
            $table->unique(['collection_id', 'concept_id']);
        });
    }

    private function createSupportTables(): void
    {
        // Historique des modifications
        Schema::create('concept_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('concept_id');
            $table->enum('action_type', ['created', 'updated', 'deprecated', 'merged', 'split']);
            $table->json('old_values')->nullable(); // Anciennes valeurs
            $table->json('new_values')->nullable(); // Nouvelles valeurs
            $table->string('user_id', 100)->nullable(); // Utilisateur ayant fait la modification
            $table->text('change_note')->nullable(); // Note sur le changement
            $table->timestamps();

            $table->foreign('concept_id')->references('id')->on('concepts')->onDelete('cascade');
            $table->index(['concept_id', 'action_type']);
        });

        // Table de configuration des propriétés personnalisées par schéma
        Schema::create('scheme_properties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('concept_scheme_id');
            $table->string('property_name', 100); // Nom de la propriété
            $table->string('property_uri', 500)->nullable(); // URI de la propriété
            $table->enum('property_type', ['string', 'text', 'integer', 'float', 'boolean', 'date', 'uri']);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_multiple')->default(false); // Peut avoir plusieurs valeurs
            $table->text('description')->nullable();
            $table->json('validation_rules')->nullable(); // Règles de validation
            $table->timestamps();

            $table->foreign('concept_scheme_id')->references('id')->on('concept_schemes')->onDelete('cascade');
            $table->unique(['concept_scheme_id', 'property_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheme_properties');
        Schema::dropIfExists('concept_history');
        Schema::dropIfExists('collection_members');
        Schema::dropIfExists('collections');
        Schema::dropIfExists('external_alignments');
        Schema::dropIfExists('translations');
        Schema::dropIfExists('mapping_relations');
        Schema::dropIfExists('associative_relations');
        Schema::dropIfExists('hierarchical_relations');
        Schema::dropIfExists('alternative_labels');
        Schema::dropIfExists('xl_labels');
        Schema::dropIfExists('concepts');
        Schema::dropIfExists('concept_schemes');
    }
};
