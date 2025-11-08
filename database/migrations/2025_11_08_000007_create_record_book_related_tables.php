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
        // Table des auteurs
        Schema::create('record_book_authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')
                ->constrained('record_books')
                ->onDelete('cascade')
                ->comment('Livre');

            $table->string('name')->comment('Nom complet de l\'auteur');
            $table->string('role')->default('author')->comment('Rôle (author, editor, translator, etc.)');
            $table->integer('display_order')->default(0)->comment('Ordre d\'affichage');

            $table->timestamps();

            $table->index('book_id');
            $table->index('name');
        });

        // Table des exemplaires
        Schema::create('record_book_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')
                ->constrained('record_books')
                ->onDelete('cascade')
                ->comment('Livre');

            $table->string('barcode')->unique()->comment('Code-barres unique');
            $table->string('call_number')->nullable()->comment('Cote de rangement');

            // Localisation
            $table->string('location')->nullable()->comment('Bibliothèque/Salle');
            $table->string('shelf')->nullable()->comment('Étagère/Rayon');

            // État et disponibilité
            $table->enum('status', ['available', 'on_loan', 'reserved', 'in_repair', 'lost', 'withdrawn'])
                ->default('available')
                ->comment('Statut de l\'exemplaire');
            $table->enum('condition', ['excellent', 'good', 'fair', 'poor'])
                ->default('good')
                ->comment('État physique');

            // Acquisition
            $table->date('acquisition_date')->nullable()->comment('Date d\'acquisition');
            $table->decimal('acquisition_price', 10, 2)->nullable()->comment('Prix d\'acquisition');
            $table->string('acquisition_source')->nullable()->comment('Source/Fournisseur');

            // Prêt en cours
            $table->boolean('is_on_loan')->default(false)->comment('En prêt actuellement');
            $table->foreignId('current_loan_id')
                ->nullable()
                ->constrained('record_book_loans')
                ->onDelete('set null')
                ->comment('Prêt en cours');

            $table->text('notes')->nullable()->comment('Notes diverses');

            $table->timestamps();
            $table->softDeletes();

            $table->index('book_id');
            $table->index('barcode');
            $table->index('status');
            $table->index('is_on_loan');
            $table->index('location');
        });

        // Table des prêts
        Schema::create('record_book_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('copy_id')
                ->constrained('record_book_copies')
                ->onDelete('cascade')
                ->comment('Exemplaire emprunté');

            $table->foreignId('borrower_id')
                ->constrained('users')
                ->comment('Emprunteur');

            $table->date('loan_date')->comment('Date du prêt');
            $table->date('due_date')->comment('Date de retour prévue');
            $table->date('return_date')->nullable()->comment('Date de retour effective');

            $table->enum('status', ['active', 'returned', 'overdue', 'renewed', 'lost'])
                ->default('active')
                ->comment('Statut du prêt');
            $table->integer('renewal_count')->default(0)->comment('Nombre de renouvellements');

            // Pénalités
            $table->decimal('late_fee', 10, 2)->default(0)->comment('Frais de retard');
            $table->boolean('fee_paid')->default(false)->comment('Frais payés');

            $table->text('notes')->nullable()->comment('Notes diverses');

            $table->foreignId('librarian_id')->nullable()->constrained('users')->comment('Bibliothécaire ayant enregistré le prêt');

            $table->timestamps();

            $table->index('copy_id');
            $table->index('borrower_id');
            $table->index('status');
            $table->index('loan_date');
            $table->index('due_date');
        });

        // Table des réservations
        Schema::create('record_book_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')
                ->constrained('record_books')
                ->onDelete('cascade')
                ->comment('Livre réservé');

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Utilisateur ayant réservé');

            $table->date('reservation_date')->comment('Date de la réservation');
            $table->date('expiry_date')->nullable()->comment('Date d\'expiration de la réservation');

            $table->enum('status', ['pending', 'ready', 'fulfilled', 'cancelled', 'expired'])
                ->default('pending')
                ->comment('Statut de la réservation');

            $table->foreignId('copy_id')
                ->nullable()
                ->constrained('record_book_copies')
                ->onDelete('set null')
                ->comment('Exemplaire assigné');

            $table->timestamp('notified_at')->nullable()->comment('Date de notification à l\'utilisateur');
            $table->text('notes')->nullable()->comment('Notes diverses');

            $table->timestamps();

            $table->index('book_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('reservation_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_book_reservations');
        Schema::dropIfExists('record_book_loans');
        Schema::dropIfExists('record_book_copies');
        Schema::dropIfExists('record_book_authors');
    }
};
