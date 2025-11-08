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
        Schema::create('record_artifacts', function (Blueprint $table) {
            $table->id();

            // Identification
            $table->string('code')->unique()->comment('Numéro d\'inventaire unique');
            $table->string('name')->comment('Nom de l\'objet');
            $table->text('description')->nullable()->comment('Description détaillée');

            // Classification
            $table->string('category')->nullable()->comment('Catégorie (peinture, sculpture, etc.)');
            $table->string('sub_category')->nullable()->comment('Sous-catégorie');
            $table->string('material')->nullable()->comment('Matériaux constitutifs');
            $table->string('technique')->nullable()->comment('Technique de fabrication');

            // Dimensions
            $table->decimal('height', 10, 2)->nullable()->comment('Hauteur en cm');
            $table->decimal('width', 10, 2)->nullable()->comment('Largeur en cm');
            $table->decimal('depth', 10, 2)->nullable()->comment('Profondeur en cm');
            $table->decimal('weight', 10, 3)->nullable()->comment('Poids en kg');
            $table->string('dimensions_notes')->nullable()->comment('Notes sur les dimensions');

            // Origine et datation
            $table->string('origin')->nullable()->comment('Provenance géographique');
            $table->string('period')->nullable()->comment('Période historique');
            $table->integer('date_start')->nullable()->comment('Année de début');
            $table->integer('date_end')->nullable()->comment('Année de fin');
            $table->string('date_precision')->nullable()->comment('circa, exact, avant, après');

            // Auteur/Créateur
            $table->string('author')->nullable()->comment('Nom de l\'auteur');
            $table->string('author_role')->nullable()->comment('artiste, sculpteur, etc.');
            $table->date('author_birth_date')->nullable()->comment('Date de naissance de l\'auteur');
            $table->date('author_death_date')->nullable()->comment('Date de décès de l\'auteur');

            // Acquisition
            $table->string('acquisition_method')->nullable()->comment('achat, don, legs, échange, etc.');
            $table->date('acquisition_date')->nullable()->comment('Date d\'acquisition');
            $table->decimal('acquisition_price', 12, 2)->nullable()->comment('Prix d\'acquisition');
            $table->string('acquisition_source')->nullable()->comment('Source/Vendeur');

            // État de conservation
            $table->enum('conservation_state', ['excellent', 'good', 'fair', 'poor', 'critical'])
                ->default('good')
                ->comment('État de conservation');
            $table->text('conservation_notes')->nullable()->comment('Notes sur l\'état');
            $table->date('last_conservation_check')->nullable()->comment('Dernière vérification');
            $table->date('next_conservation_check')->nullable()->comment('Prochaine vérification prévue');

            // Localisation
            $table->string('current_location')->nullable()->comment('Salle/Réserve actuelle');
            $table->string('storage_location')->nullable()->comment('Emplacement de stockage détaillé');
            $table->boolean('is_on_display')->default(false)->comment('En exposition');
            $table->boolean('is_on_loan')->default(false)->comment('En prêt');

            // Valeurs
            $table->decimal('estimated_value', 12, 2)->nullable()->comment('Valeur estimée');
            $table->decimal('insurance_value', 12, 2)->nullable()->comment('Valeur d\'assurance');
            $table->date('valuation_date')->nullable()->comment('Date d\'évaluation');

            // Métadonnées et configuration
            $table->json('metadata')->nullable()->comment('Métadonnées personnalisées');
            $table->enum('access_level', ['public', 'internal', 'confidential', 'secret'])
                ->default('internal')
                ->comment('Niveau d\'accès');
            $table->enum('status', ['active', 'in_restoration', 'on_loan', 'deaccessioned', 'lost', 'destroyed'])
                ->default('active')
                ->comment('Statut de l\'objet');

            // Relations organisationnelles
            $table->foreignId('creator_id')->constrained('users')->comment('Créateur de la fiche');
            $table->foreignId('organisation_id')->constrained('organisations')->comment('Organisation propriétaire');

            // Dates et timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('code');
            $table->index('category');
            $table->index('status');
            $table->index('conservation_state');
            $table->index('is_on_display');
            $table->index('is_on_loan');
            $table->index('creator_id');
            $table->index('organisation_id');
            $table->index(['organisation_id', 'status']);
            $table->fullText(['name', 'description', 'author']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_artifacts');
    }
};
