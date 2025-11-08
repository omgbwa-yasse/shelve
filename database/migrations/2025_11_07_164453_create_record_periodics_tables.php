<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Table principale: Périodiques (revues, magazines, journaux)
        Schema::create('record_periodics', function (Blueprint $table) {
            $table->id();

            // Identification
            $table->string('code')->unique()->comment('Code unique PER-YYYY-NNNN');
            $table->string('title')->comment('Titre de la publication');
            $table->string('subtitle')->nullable()->comment('Sous-titre');
            $table->text('description')->nullable()->comment('Description');

            // Identifiants standards
            $table->string('issn')->nullable()->unique()->comment('ISSN (International Standard Serial Number)');
            $table->string('eissn')->nullable()->comment('eISSN pour version électronique');

            // Classification
            $table->string('type')->nullable()->comment('revue, magazine, journal, newsletter');
            $table->string('subject_area')->nullable()->comment('Domaine thématique');
            $table->json('keywords')->nullable()->comment('Mots-clés');

            // Publication
            $table->string('publisher')->nullable()->comment('Éditeur');
            $table->string('publisher_location')->nullable()->comment('Lieu d\'édition');
            $table->string('language')->default('fr')->comment('Langue principale');
            $table->string('frequency')->nullable()->comment('mensuel, bimensuel, trimestriel, annuel');

            // Dates
            $table->integer('first_year')->nullable()->comment('Année de première publication');
            $table->integer('last_year')->nullable()->comment('Année de dernière publication (si cessé)');
            $table->boolean('is_active')->default(true)->comment('Toujours publié');

            // Contact et URLs
            $table->string('website')->nullable()->comment('Site web');
            $table->string('contact_email')->nullable()->comment('Email de contact');

            // Métadonnées
            $table->json('metadata')->nullable()->comment('Métadonnées personnalisées');
            $table->enum('access_level', ['public', 'internal', 'confidential'])->default('public');
            $table->enum('status', ['active', 'ceased', 'suspended', 'archived'])->default('active');

            // Relations
            $table->foreignId('creator_id')->constrained('users')->comment('Créateur de la fiche');
            $table->foreignId('organisation_id')->constrained('organisations');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('code');
            $table->index('issn');
            $table->index('type');
            $table->index('status');
            $table->index(['organisation_id', 'status']);
            $table->fullText(['title', 'subtitle', 'description', 'publisher']);
        });

        // Table: Numéros/Issues de périodiques
        Schema::create('record_periodic_issues', function (Blueprint $table) {
            $table->id();

            // Identification
            $table->foreignId('periodic_id')->constrained('record_periodics')->onDelete('cascade');
            $table->string('issue_number')->comment('Numéro');
            $table->string('volume')->nullable()->comment('Volume');
            $table->string('year')->comment('Année');

            // Dates
            $table->date('publication_date')->nullable()->comment('Date de publication');
            $table->string('season')->nullable()->comment('Saison (printemps, été, automne, hiver)');

            // Contenu
            $table->string('title')->nullable()->comment('Titre du numéro (thématique)');
            $table->text('summary')->nullable()->comment('Sommaire/Résumé');
            $table->integer('page_count')->nullable()->comment('Nombre de pages');

            // Identification numéro
            $table->string('doi')->nullable()->comment('DOI du numéro');
            $table->string('cover_image_path')->nullable()->comment('Chemin vers image de couverture');

            // État
            $table->enum('status', ['expected', 'received', 'catalogued', 'archived', 'missing'])->default('expected');
            $table->date('received_date')->nullable()->comment('Date de réception');

            // Localisation
            $table->string('location')->nullable()->comment('Localisation physique');
            $table->string('call_number')->nullable()->comment('Cote');

            // Métadonnées
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes et contraintes
            $table->index('issue_number');
            $table->index('publication_date');
            $table->index('status');
            $table->index('periodic_id');
        });

        // Ajout de l'index unique après création de la table pour limiter la longueur
        DB::statement('ALTER TABLE record_periodic_issues ADD UNIQUE unique_issue (periodic_id, volume(50), issue_number(50), year(10))');

        // Table: Articles de périodiques
        Schema::create('record_periodic_articles', function (Blueprint $table) {
            $table->id();

            // Identification
            $table->foreignId('issue_id')->constrained('record_periodic_issues')->onDelete('cascade');
            $table->foreignId('periodic_id')->constrained('record_periodics')->onDelete('cascade');

            // Contenu
            $table->string('title')->comment('Titre de l\'article');
            $table->text('abstract')->nullable()->comment('Résumé');
            $table->json('authors')->comment('Auteurs [{"name": "...", "affiliation": "..."}]');

            // Localisation dans la revue
            $table->string('page_start')->nullable()->comment('Page de début');
            $table->string('page_end')->nullable()->comment('Page de fin');
            $table->string('section')->nullable()->comment('Section/Rubrique');

            // Identifiants
            $table->string('doi')->nullable()->unique()->comment('DOI de l\'article');
            $table->string('url')->nullable()->comment('URL texte intégral');

            // Classification
            $table->json('keywords')->nullable()->comment('Mots-clés');
            $table->string('language')->default('fr')->comment('Langue de l\'article');
            $table->string('article_type')->nullable()->comment('recherche, revue, éditorial, etc.');

            // Métadonnées
            $table->json('metadata')->nullable();
            $table->boolean('is_peer_reviewed')->default(false)->comment('Article à comité de lecture');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('doi');
            $table->index(['periodic_id', 'issue_id']);
            $table->fullText(['title', 'abstract']);
        });

        // Table: Abonnements
        Schema::create('record_periodic_subscriptions', function (Blueprint $table) {
            $table->id();

            // Identification
            $table->foreignId('periodic_id')->constrained('record_periodics')->onDelete('cascade');
            $table->string('subscription_number')->nullable()->comment('Numéro d\'abonnement');

            // Dates
            $table->date('start_date')->comment('Date de début');
            $table->date('end_date')->comment('Date de fin');
            $table->boolean('auto_renewal')->default(false)->comment('Renouvellement automatique');

            // Financier
            $table->decimal('cost', 10, 2)->comment('Coût');
            $table->string('currency')->default('EUR')->comment('Devise');
            $table->string('payment_method')->nullable()->comment('Méthode de paiement');
            $table->string('invoice_number')->nullable()->comment('Numéro de facture');

            // Fournisseur
            $table->string('supplier')->comment('Fournisseur/Agence');
            $table->string('supplier_contact')->nullable()->comment('Contact fournisseur');

            // Type et accès
            $table->enum('subscription_type', ['print', 'online', 'print_online'])->default('print');
            $table->text('access_notes')->nullable()->comment('Notes sur l\'accès');

            // État
            $table->enum('status', ['active', 'expired', 'cancelled', 'pending'])->default('pending');
            $table->text('notes')->nullable()->comment('Notes');

            // Relations
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->comment('Responsable');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('periodic_id');
            $table->index('status');
            $table->index('end_date');
            $table->index(['periodic_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_periodic_subscriptions');
        Schema::dropIfExists('record_periodic_articles');
        Schema::dropIfExists('record_periodic_issues');
        Schema::dropIfExists('record_periodics');
    }
};
