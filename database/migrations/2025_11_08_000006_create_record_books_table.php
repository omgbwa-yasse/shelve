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

            // ZONE 0 : Type de document et médiation
            $table->string('typdoc', 2)->default('a')->comment('Type de document (a=monographie, s=périodique)');
            $table->char('statut', 1)->default('x')->comment('Statut (x=complet, y=provisoire, r=rétro)');
            $table->string('forme_contenu', 100)->nullable()->comment('Forme du contenu (ex: Texte)');
            $table->string('type_mediation', 100)->nullable()->comment('Type de médiation (ex: visuel : immédiat)');

            // ZONE 1 : Identification bibliographique - Titre et mention de responsabilité
            $table->string('isbn')->nullable()->unique()->comment('ISBN-10 ou ISBN-13');
            $table->text('title')->comment('Titre propre du livre');
            $table->text('titre_parallele')->nullable()->comment('Titre parallèle');
            $table->string('subtitle')->nullable()->comment('Sous-titre');
            $table->text('complement_titre')->nullable()->comment('Complément de titre');
            $table->text('titre_cle')->nullable()->comment('Titre clé');

            // ZONE 2 : Édition
            $table->string('mention_edition')->nullable()->comment('Mention d\'édition');
            $table->text('mention_resp_edition')->nullable()->comment('Mention de responsabilité d\'édition');

            // ZONE 3 : Spécifique
            $table->text('zone_specifique')->nullable()->comment('Zone spécifique (cartes, musique, etc.)');

            // ZONE 4 : Adresse bibliographique
            $table->string('publisher')->nullable()->comment('Éditeur (legacy)');
            $table->string('annee_publication', 10)->nullable()->comment('Année principale de publication');
            $table->string('date_publication', 50)->nullable()->comment('Date complète de publication');
            $table->string('date_depot_legal', 50)->nullable()->comment('Date de dépôt légal');
            $table->string('date_copyright', 50)->nullable()->comment('Date de copyright');
            $table->integer('publication_year')->nullable()->comment('Année de publication (legacy)');
            $table->string('edition')->nullable()->comment('Édition (1ère, 2ème, etc.) (legacy)');
            $table->string('place_of_publication')->nullable()->comment('Lieu de publication (legacy)');

            // Classification (sera déplacée vers la table classifications)
            $table->string('dewey')->nullable()->comment('Classification Dewey (legacy - utiliser table classifications)');
            $table->string('lcc')->nullable()->comment('Library of Congress Classification (legacy)');
            $table->json('subjects')->nullable()->comment('Sujets/Thèmes (JSON array - legacy, utiliser table categories)');

            // ZONE 5 : Collation - Description physique
            $table->string('importance_materielle')->nullable()->comment('Importance matérielle (ex: 1 vol. (197 p.))');
            $table->text('autre_materiel')->nullable()->comment('Autre matériel (illustrations, etc.)');
            $table->string('format_dimensions', 50)->nullable()->comment('Format et dimensions (ex: 17 cm)');
            $table->text('materiel_accompagnement')->nullable()->comment('Matériel d\'accompagnement');
            $table->integer('pages')->nullable()->comment('Nombre de pages');
            $table->string('format')->nullable()->comment('Format (in-8, in-4, A4, etc.) (legacy)');
            $table->string('binding')->nullable()->comment('Reliure (broché, relié, etc.) (legacy)');
            $table->string('language')->default('fr')->comment('Langue (ISO 639-1) (legacy)');
            $table->string('dimensions')->nullable()->comment('Dimensions (HxLxP en cm) (legacy)');

            // ZONE 7 : Notes
            $table->text('notes_generales')->nullable()->comment('Notes générales');
            $table->text('notes_contenu')->nullable()->comment('Notes de contenu');
            $table->text('notes_bibliographie')->nullable()->comment('Notes de bibliographie');
            $table->text('notes_resume')->nullable()->comment('Notes de résumé');
            $table->text('notes_public_destine')->nullable()->comment('Notes sur le public destiné');
            $table->text('description')->nullable()->comment('Résumé/Description (legacy)');
            $table->text('table_of_contents')->nullable()->comment('Table des matières (legacy)');
            $table->text('notes')->nullable()->comment('Notes diverses (legacy)');

            // ZONE 8 : Numéros d'identification
            $table->string('isbn_errone', 20)->nullable()->comment('ISBN erroné');
            $table->string('ean', 20)->nullable()->comment('Code EAN');
            $table->string('issn', 20)->nullable()->comment('ISSN (série continue)');
            $table->string('numero_editeur', 100)->nullable()->comment('Numéro propre de l\'éditeur');
            $table->text('autre_numero')->nullable()->comment('Autres numéros');
            $table->string('prix', 50)->nullable()->comment('Prix');
            $table->text('disponibilite')->nullable()->comment('Disponibilité');

            // Métadonnées UNIMARC
            $table->string('code_langue', 10)->nullable()->comment('Code langue ISO');
            $table->string('code_pays', 10)->nullable()->comment('Code pays ISO');
            $table->string('catalogueur', 100)->nullable()->comment('Catalogueur');
            $table->string('source_notice', 50)->nullable()->comment('Source de la notice (SUDOC, BNF, etc.)');
            $table->string('ppn', 20)->nullable()->comment('Numéro PPN du SUDOC');

            // Collection/Série (legacy)
            $table->string('series')->nullable()->comment('Nom de la collection/série (legacy)');
            $table->integer('series_number')->nullable()->comment('Numéro dans la série (legacy)');

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
            $table->index('ean');
            $table->index('issn');
            $table->index('numero_editeur');
            $table->index('ppn');
            $table->index('annee_publication');
            $table->index('publication_year');
            $table->index('publisher');
            $table->index('dewey');
            $table->index('typdoc');
            $table->index('statut');
            $table->index('status');
            $table->index('creator_id');
            $table->index('organisation_id');
            $table->index(['organisation_id', 'status']);
            $table->fullText(['title', 'subtitle', 'description'], 'books_search_fulltext');
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
