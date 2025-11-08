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
        Schema::create('record_periodicals', function (Blueprint $table) {
            $table->id();

            // Identification
            $table->string('issn')->nullable()->unique()->comment('ISSN (International Standard Serial Number)');
            $table->string('title')->comment('Titre du périodique');
            $table->string('subtitle')->nullable()->comment('Sous-titre');
            $table->string('abbreviated_title')->nullable()->comment('Titre abrégé');

            // Édition
            $table->string('publisher')->nullable()->comment('Éditeur');
            $table->string('place_of_publication')->nullable()->comment('Lieu de publication');
            $table->integer('start_year')->nullable()->comment('Année de première publication');
            $table->integer('end_year')->nullable()->comment('Année de fin (si arrêté)');

            // Classification
            $table->string('dewey')->nullable()->comment('Classification Dewey');
            $table->string('lcc')->nullable()->comment('Library of Congress Classification');
            $table->json('subjects')->nullable()->comment('Sujets/Thèmes (JSON array)');

            // Périodicité
            $table->enum('frequency', [
                'daily',
                'weekly',
                'biweekly',
                'monthly',
                'bimonthly',
                'quarterly',
                'semiannual',
                'annual',
                'irregular'
            ])->default('monthly')->comment('Fréquence de parution');
            $table->string('frequency_details')->nullable()->comment('Détails de la fréquence');

            // Type et format
            $table->enum('periodical_type', [
                'magazine',
                'journal',
                'newspaper',
                'bulletin',
                'review',
                'newsletter'
            ])->default('magazine')->comment('Type de périodique');
            $table->string('format')->nullable()->comment('Format physique');
            $table->string('language')->default('fr')->comment('Langue principale (ISO 639-1)');

            // Abonnement
            $table->boolean('is_subscribed')->default(false)->comment('Abonnement actif');
            $table->date('subscription_start')->nullable()->comment('Date de début d\'abonnement');
            $table->date('subscription_end')->nullable()->comment('Date de fin d\'abonnement');
            $table->decimal('subscription_price', 10, 2)->nullable()->comment('Prix de l\'abonnement annuel');
            $table->string('supplier')->nullable()->comment('Fournisseur/Diffuseur');

            // Description
            $table->text('description')->nullable()->comment('Description du périodique');
            $table->text('scope')->nullable()->comment('Portée et couverture thématique');
            $table->string('website')->nullable()->comment('Site web');
            $table->string('editor_in_chief')->nullable()->comment('Rédacteur en chef');

            // Statistiques
            $table->integer('total_issues')->default(0)->comment('Nombre total de numéros');
            $table->integer('available_issues')->default(0)->comment('Numéros disponibles');
            $table->integer('loan_count')->default(0)->comment('Nombre total de prêts');

            // Métadonnées et configuration
            $table->json('metadata')->nullable()->comment('Métadonnées personnalisées');
            $table->enum('access_level', ['public', 'internal', 'confidential', 'secret'])
                ->default('public')
                ->comment('Niveau d\'accès');
            $table->enum('status', ['active', 'suspended', 'ceased', 'archived'])
                ->default('active')
                ->comment('Statut du périodique');

            // Relations organisationnelles
            $table->foreignId('creator_id')->constrained('users')->comment('Créateur de la fiche');
            $table->foreignId('organisation_id')->constrained('organisations')->comment('Organisation propriétaire');

            // Dates et timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('issn');
            $table->index('publisher');
            $table->index('frequency');
            $table->index('periodical_type');
            $table->index('is_subscribed');
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
        Schema::dropIfExists('record_periodicals');
    }
};
