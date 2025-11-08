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
        // Table des numéros/volumes
        Schema::create('record_periodical_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periodical_id')
                ->constrained('record_periodicals')
                ->onDelete('cascade')
                ->comment('Périodique parent');

            // Identification du numéro
            $table->integer('volume')->nullable()->comment('Numéro de volume');
            $table->integer('issue_number')->nullable()->comment('Numéro du fascicule');
            $table->string('special_issue')->nullable()->comment('Numéro spécial (ex: "Hors-série été 2025")');

            // Dates
            $table->date('publication_date')->comment('Date de parution');
            $table->integer('publication_year')->comment('Année de parution');
            $table->integer('publication_month')->nullable()->comment('Mois de parution (1-12)');

            // Description physique
            $table->integer('pages')->nullable()->comment('Nombre de pages');
            $table->string('cover_theme')->nullable()->comment('Thème de couverture');
            $table->text('table_of_contents')->nullable()->comment('Sommaire');

            // Réception
            $table->date('receipt_date')->nullable()->comment('Date de réception');
            $table->enum('receipt_status', ['expected', 'received', 'late', 'missing', 'claimed'])
                ->default('expected')
                ->comment('Statut de réception');

            // Disponibilité
            $table->enum('status', ['available', 'on_loan', 'binding', 'lost', 'withdrawn'])
                ->default('available')
                ->comment('Statut du numéro');
            $table->boolean('is_on_loan')->default(false)->comment('En prêt actuellement');
            $table->foreignId('current_loan_id')
                ->nullable()
                ->constrained('record_periodical_loans')
                ->onDelete('set null')
                ->comment('Prêt en cours');

            // Localisation
            $table->string('location')->nullable()->comment('Localisation physique');
            $table->string('shelf')->nullable()->comment('Étagère');
            $table->string('barcode')->nullable()->unique()->comment('Code-barres');

            $table->text('notes')->nullable()->comment('Notes diverses');

            $table->timestamps();
            $table->softDeletes();

            $table->index('periodical_id');
            $table->index('publication_date');
            $table->index('publication_year');
            $table->index('status');
            $table->index('receipt_status');
            $table->index('is_on_loan');
            $table->index(['periodical_id', 'volume', 'issue_number']);
        });

        // Table des prêts de périodiques
        Schema::create('record_periodical_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')
                ->constrained('record_periodical_issues')
                ->onDelete('cascade')
                ->comment('Numéro emprunté');

            $table->foreignId('borrower_id')
                ->constrained('users')
                ->comment('Emprunteur');

            $table->date('loan_date')->comment('Date du prêt');
            $table->date('due_date')->comment('Date de retour prévue');
            $table->date('return_date')->nullable()->comment('Date de retour effective');

            $table->enum('status', ['active', 'returned', 'overdue', 'lost'])
                ->default('active')
                ->comment('Statut du prêt');

            $table->decimal('late_fee', 10, 2)->default(0)->comment('Frais de retard');
            $table->boolean('fee_paid')->default(false)->comment('Frais payés');

            $table->text('notes')->nullable()->comment('Notes diverses');

            $table->foreignId('librarian_id')->nullable()->constrained('users')->comment('Bibliothécaire');

            $table->timestamps();

            $table->index('issue_id');
            $table->index('borrower_id');
            $table->index('status');
            $table->index('loan_date');
            $table->index('due_date');
        });

        // Table des abonnements
        Schema::create('record_periodical_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periodical_id')
                ->constrained('record_periodicals')
                ->onDelete('cascade')
                ->comment('Périodique');

            $table->string('subscription_number')->nullable()->comment('Numéro d\'abonnement');
            $table->date('start_date')->comment('Date de début');
            $table->date('end_date')->comment('Date de fin');

            $table->enum('subscription_type', ['individual', 'institutional', 'trial', 'gift'])
                ->default('institutional')
                ->comment('Type d\'abonnement');

            $table->decimal('price', 10, 2)->comment('Prix');
            $table->string('currency', 3)->default('EUR')->comment('Devise (ISO 4217)');

            $table->string('supplier')->nullable()->comment('Fournisseur');
            $table->string('order_number')->nullable()->comment('Numéro de commande');

            $table->enum('status', ['active', 'pending', 'expired', 'cancelled', 'suspended'])
                ->default('active')
                ->comment('Statut de l\'abonnement');

            $table->boolean('auto_renew')->default(false)->comment('Renouvellement automatique');
            $table->date('renewal_date')->nullable()->comment('Date de renouvellement');

            $table->text('notes')->nullable()->comment('Notes diverses');

            $table->timestamps();

            $table->index('periodical_id');
            $table->index('start_date');
            $table->index('end_date');
            $table->index('status');
        });

        // Table des réclamations
        Schema::create('record_periodical_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periodical_id')
                ->constrained('record_periodicals')
                ->onDelete('cascade')
                ->comment('Périodique');

            $table->foreignId('issue_id')
                ->nullable()
                ->constrained('record_periodical_issues')
                ->onDelete('cascade')
                ->comment('Numéro réclamé');

            $table->date('claim_date')->comment('Date de réclamation');
            $table->enum('claim_type', ['missing', 'damaged', 'late', 'wrong_issue'])
                ->comment('Type de réclamation');

            $table->text('description')->nullable()->comment('Description du problème');

            $table->enum('status', ['pending', 'sent', 'resolved', 'cancelled'])
                ->default('pending')
                ->comment('Statut de la réclamation');

            $table->date('resolution_date')->nullable()->comment('Date de résolution');
            $table->text('resolution_notes')->nullable()->comment('Notes de résolution');

            $table->foreignId('claimed_by')
                ->constrained('users')
                ->comment('Utilisateur ayant fait la réclamation');

            $table->timestamps();

            $table->index('periodical_id');
            $table->index('issue_id');
            $table->index('claim_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_periodical_claims');
        Schema::dropIfExists('record_periodical_subscriptions');
        Schema::dropIfExists('record_periodical_loans');
        Schema::dropIfExists('record_periodical_issues');
    }
};
