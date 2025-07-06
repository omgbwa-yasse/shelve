<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateThesaurusSkosSchema extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * Table : thesaurus_schemes
         * Représente un skos:ConceptScheme (Thésaurus ou Vocabulaire Contrôlé).
         * L'URI est ajustée à 255 caractères pour assurer la compatibilité des index uniques
         * par défaut dans MySQL avec utf8mb4.
         */
        Schema::create('thesaurus_schemes', function (Blueprint $table) {
            $table->id();
            $table->string('uri', 255)->unique()->comment('URI du schéma SKOS (ajustée pour compatibilité d\'index)');
            $table->string('identifier', 255)->nullable()->comment('Identifiant externe (ARK, DOI, etc.)');
            $table->string('title', 500)->nullable()->comment('Titre principal du schéma');
            $table->text('description')->nullable()->comment('Description du schéma');
            $table->string('language', 10)->default('fr-fr')->comment('Langue par défaut du schéma');
            $table->text('dc_relation')->nullable()->comment('Propriété Dublin Core "Relation"');
            $table->text('dc_source')->nullable()->comment('Propriété Dublin Core "Source"');
            $table->string('issued', 255)->nullable()->comment('Date d\'émission ou de publication (format flexible)');
            $table->timestamps(); // Ajoute created_at et updated_at

            $table->index('uri'); // Index supplémentaire pour les recherches rapides
            $table->index('identifier');
        });

        /**
         * Table : thesaurus_concepts
         * Représente un skos:Concept (Terme ou Concept dans le thésaurus).
         * L'URI est ajustée à 255 caractères pour la même raison de compatibilité d'index.
         */
        Schema::create('thesaurus_concepts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheme_id')
                  ->constrained('thesaurus_schemes')
                  ->onDelete('cascade')
                  ->comment('Clé étrangère vers le schéma auquel le concept appartient');
            $table->string('uri', 255)->unique()->comment('URI unique du concept SKOS (ajustée pour compatibilité d\'index)');
            $table->string('notation', 100)->nullable()->comment('Notation (code alphanumérique) du concept');
            $table->tinyInteger('status')->default(1)->comment('Statut du concept (ex: 1=active, 0=deprecated)');
            $table->timestamps(); // Ajoute created_at et updated_at

            $table->index('notation');
            $table->index(['scheme_id', 'notation']);
        });

        /**
         * Table : thesaurus_labels
         * Représente un skos-xl:Label (Forme littérale d'un concept).
         * L'URI de label est nullable et n'est pas unique, donc elle peut conserver une longueur de 500
         * si des URIs plus longues sont attendues pour les labels spécifiques.
         */
        Schema::create('thesaurus_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('concept_id')
                  ->constrained('thesaurus_concepts')
                  ->onDelete('cascade')
                  ->comment('Clé étrangère vers le concept associé à ce label');
            $table->string('uri', 500)->nullable()->comment('URI du skos-xl:Label (si applicable, non unique)');
            $table->enum('label_type', ['prefLabel', 'altLabel', 'hiddenLabel'])
                  ->comment('Type de label SKOS (Préféré, Alternatif, Caché)');
            $table->string('literal_form', 500)->comment('La forme littérale du label');
            $table->string('language', 10)->default('fr-fr')->comment('Langue du label');
            $table->tinyInteger('status')->default(1)->comment('Statut du label (ex: 1=active, 0=inactive). Utile si un label peut être désactivé indépendamment du concept.');
            $table->timestamps(); // Ajoute created_at et updated_at

            $table->index('label_type');
            $table->index('literal_form');
            // Empêche des labels identiques (type, forme, langue) pour un même concept
            $table->unique(['concept_id', 'label_type', 'language', 'literal_form'], 'unique_concept_label');
        });

        /**
         * Table : thesaurus_concept_relations
         * Gère toutes les relations SKOS entre concepts (Broader, Narrower, Related, Mappings, etc.).
         * La table thesaurus_ginco_relations a été fusionnée ici.
         */
        Schema::create('thesaurus_concept_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_concept_id')
                  ->constrained('thesaurus_concepts')
                  ->onDelete('cascade')
                  ->comment('ID du concept source de la relation');
            $table->foreignId('target_concept_id')
                  ->constrained('thesaurus_concepts')
                  ->onDelete('cascade')
                  ->comment('ID du concept cible de la relation');
            $table->enum('relation_type', [
                'broader', 'narrower', 'related',
                'broadMatch', 'narrowMatch', 'relatedMatch',
                'exactMatch', 'closeMatch',
                'inScheme', // Utile si un concept est explicitement lié à un schéma via une relation conceptuelle
                'TermeAssocie' // Relation spécifique Ginco intégrée
            ])->comment('Type de relation SKOS ou spécifique');
            $table->timestamps(); // Ajoute created_at et updated_at

            // Empêche les relations dupliquées (même source, cible, type)
            $table->unique(['source_concept_id', 'target_concept_id', 'relation_type'], 'unique_concept_relation');
            $table->index('relation_type');
            $table->index(['source_concept_id', 'relation_type']);
            $table->index(['target_concept_id', 'relation_type']);
        });

        /**
         * Table : thesaurus_concept_notes
         * Notes, définitions et exemples associés aux concepts.
         */
        Schema::create('thesaurus_concept_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('concept_id')
                  ->constrained('thesaurus_concepts')
                  ->onDelete('cascade')
                  ->comment('Clé étrangère vers le concept associé à cette note');
            $table->enum('note_type', [
                'scopeNote', 'definition', 'example',
                'historyNote', 'editorialNote', 'changeNote'
            ])->comment('Type de note SKOS');
            $table->text('note_text')->comment('Le contenu de la note');
            $table->string('language', 10)->default('fr-fr')->comment('Langue de la note');
            $table->timestamps(); // Ajoute created_at et updated_at

            $table->index('note_type');
            // Empêche plusieurs notes du même type/langue pour un concept
            $table->unique(['concept_id', 'note_type', 'language'], 'unique_concept_note');
        });

        /**
         * Table : thesaurus_top_concepts
         * Définit explicitement les Top Concepts d'un skos:ConceptScheme.
         * Maintenue pour sa clarté sémantique spécifique (Scheme -> Concept).
         */
        Schema::create('thesaurus_top_concepts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheme_id')
                  ->constrained('thesaurus_schemes')
                  ->onDelete('cascade')
                  ->comment('Clé étrangère vers le schéma');
            $table->foreignId('concept_id')
                  ->constrained('thesaurus_concepts')
                  ->onDelete('cascade')
                  ->comment('Clé étrangère vers le concept de tête');
            $table->timestamps(); // Ajoute created_at et updated_at

            $table->unique(['scheme_id', 'concept_id'], 'unique_top_concept');
        });

        /**
         * Table : thesaurus_organizations
         * Représente des organismes (foaf:Organization) associés aux schémas.
         * Les champs d'URI/URL sont ajustés si nécessaire.
         */
        Schema::create('thesaurus_organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 500)->comment('Nom de l\'organisation');
            $table->string('homepage', 255)->nullable()->comment('URL du site web de l\'organisation (ajustée pour l\'index si besoin)');
            $table->string('email', 255)->nullable()->comment('Adresse email de l\'organisation');
            $table->timestamps(); // Ajoute created_at et updated_at
        });

        /**
         * Table : thesaurus_scheme_organizations
         * Table pivot pour lier les thésaurus aux organisations avec un rôle spécifique.
         */
        Schema::create('thesaurus_scheme_organizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheme_id')
                  ->constrained('thesaurus_schemes')
                  ->onDelete('cascade')
                  ->comment('Clé étrangère vers le schéma');
            $table->foreignId('organization_id')
                  ->constrained('thesaurus_organizations')
                  ->onDelete('cascade')
                  ->comment('Clé étrangère vers l\'organisation');
            $table->enum('role', ['creator', 'contributor', 'publisher', 'maintainer'])
                  ->default('maintainer')
                  ->comment('Rôle de l\'organisation par rapport au schéma');
            $table->timestamps(); // Ajoute created_at et updated_at

            $table->unique(['scheme_id', 'organization_id', 'role'], 'unique_scheme_org_role');
        });

        /**
         * Table : thesaurus_concept_properties
         * Permet d'ajouter des propriétés Dublin Core étendues ou personnalisées aux concepts.
         */
        Schema::create('thesaurus_concept_properties', function (Blueprint<ctrl61> $table) {
            $table->id();
            $table->foreignId('concept_id')
                  ->constrained('thesaurus_concepts')
                  ->onDelete('cascade')
                  ->comment('Clé étrangère vers le concept');
            $table->string('property_name', 100)->comment('Nom de la propriété (ex: "dc:coverage")');
            $table->text('property_value')->comment('Valeur de la propriété (champ TEXT pour la flexibilité)');
            $table->string('language', 10)->nullable()->comment('Langue de la propriété si textuelle');
            $table->timestamps(); // Ajoute created_at et updated_at

            // Optionnel: unique(['concept_id', 'property_name', 'language']) si une propriété ne doit avoir qu'une valeur par langue
        });

        /**
         * Table : thesaurus_namespaces
         * Gère les préfixes et URIs des namespaces RDF utilisés.
         * L'URI est ajustée à 255 pour la compatibilité d'index unique par défaut.
         */
        Schema::create('thesaurus_namespaces', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 50)->unique()->comment('Préfixe du namespace (ex: "skos", "dc")');
            $table->string('namespace_uri', 255)->comment('URI complète du namespace (ajustée pour compatibilité d\'index)');
            $table->text('description')->nullable()->comment('Description du namespace');
            $table->timestamps(); // Ajoute created_at et updated_at
        });

        /**
         * Index FULLTEXT pour la recherche sur les labels et les notes.
         * Note: Ces index sont spécifiques à MySQL. Pour PostgreSQL, vous utiliseriez des index de type GIN/GIST.
         */
        DB::statement('ALTER TABLE thesaurus_labels ADD FULLTEXT INDEX ft_literal_form (literal_form)');
        DB::statement('ALTER TABLE thesaurus_concept_notes ADD FULLTEXT INDEX ft_note_text (note_text)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // L'ordre est important pour respecter les contraintes de clés étrangères lors de la suppression.
        Schema::dropIfExists('thesaurus_namespaces');
        Schema::dropIfExists('thesaurus_concept_properties');
        Schema::dropIfExists('thesaurus_scheme_organizations');
        Schema::dropIfExists('thesaurus_organizations');
        Schema::dropIfExists('thesaurus_top_concepts');
        Schema::dropIfExists('thesaurus_concept_notes');
        Schema::dropIfExists('thesaurus_concept_relations');
        Schema::dropIfExists('thesaurus_labels');
        Schema::dropIfExists('thesaurus_concepts');
        Schema::dropIfExists('thesaurus_schemes');
    }
}
