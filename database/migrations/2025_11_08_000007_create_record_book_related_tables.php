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

            // Relation avec le livre
            $table->foreignId('book_id')
                ->constrained('record_books')
                ->onDelete('cascade')
                ->comment('Livre');

            // Identification de l'exemplaire
            $table->string('barcode', 50)->unique()->comment('Code-barres unique de l\'exemplaire');
            $table->string('call_number', 100)->nullable()->comment('Cote bibliothécaire');
            $table->string('inventory_number', 50)->nullable()->comment('Numéro d\'inventaire');

            // Localisation
            $table->string('location', 200)->nullable()->comment('Bibliothèque/Salle/Rayon');
            $table->string('shelf', 100)->nullable()->comment('Étagère précise');
            $table->string('section', 100)->nullable()->comment('Section de la bibliothèque');

            // État de l'exemplaire
            $table->enum('status', ['available', 'on_loan', 'reserved', 'in_repair', 'lost', 'withdrawn', 'processing'])->default('available');
            $table->enum('condition', ['excellent', 'good', 'fair', 'poor', 'damaged'])->default('good');
            $table->text('condition_notes')->nullable()->comment('Notes sur l\'état physique');

            // Acquisition
            $table->date('acquisition_date')->nullable();
            $table->decimal('acquisition_price', 10, 2)->nullable();
            $table->string('acquisition_source', 250)->nullable()->comment('Fournisseur ou donateur');
            $table->enum('acquisition_type', ['purchase', 'donation', 'gift', 'exchange', 'deposit'])->default('purchase');

            // Prêt en cours (dénormalisation pour performance)
            $table->boolean('is_on_loan')->default(false);
            $table->unsignedBigInteger('current_loan_id')->nullable(); // Constraint added later or ignored to avoid circular dependency if loan table created after
            $table->date('due_date')->nullable()->comment('Date de retour prévue');

            // Restrictions
            $table->boolean('is_reference_only')->default(false)->comment('Consultation sur place uniquement');
            $table->boolean('is_restricted')->default(false)->comment('Accès restreint');
            $table->text('restriction_notes')->nullable();

            // Statistiques
            $table->integer('loan_count')->default(0)->comment('Nombre total de prêts');
            $table->timestamp('last_loan_date')->nullable();
            $table->timestamp('last_inventory_check')->nullable();

            // Notes
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable()->comment('Notes internes non publiques');

            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Index de performance
            $table->index('book_id');
            $table->index('barcode');
            $table->index('status');
            $table->index('is_on_loan');
            $table->index('location');
            $table->index(['book_id', 'status'], 'idx_book_status');
        });

        // Table des prêts
        Schema::create('record_book_loans', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('copy_id')
                ->constrained('record_book_copies')
                ->onDelete('cascade')
                ->comment('Exemplaire emprunté');

            $table->foreignId('borrower_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Emprunteur');

            // Dates du prêt
            $table->date('loan_date')->comment('Date du prêt');
            $table->date('due_date')->comment('Date de retour prévue');
            $table->date('return_date')->nullable()->comment('Date de retour effective');
            $table->timestamp('actual_return_time')->nullable();

            // Statut
            $table->enum('status', ['active', 'returned', 'overdue', 'renewed', 'lost', 'cancelled'])
                ->default('active')
                ->comment('Statut du prêt');

            // Renouvellements
            $table->integer('renewal_count')->default(0)->comment('Nombre de renouvellements');
            $table->integer('max_renewals')->default(3);
            $table->date('last_renewal_date')->nullable();

            // Pénalités
            $table->decimal('late_fee', 10, 2)->default(0)->comment('Frais de retard');
            $table->decimal('damage_fee', 10, 2)->default(0);
            $table->decimal('total_fee', 10, 2)->default(0);
            $table->boolean('fee_paid')->default(false)->comment('Frais payés');
            $table->date('fee_payment_date')->nullable();

            // Retard
            $table->integer('days_overdue')->default(0);
            $table->date('first_reminder_sent')->nullable();
            $table->date('second_reminder_sent')->nullable();
            $table->date('final_notice_sent')->nullable();

            // État du livre au retour
            $table->enum('return_condition', ['excellent', 'good', 'fair', 'poor', 'damaged'])->nullable();
            $table->text('return_notes')->nullable();
            $table->boolean('damage_reported')->default(false);

            // Traitement
            $table->unsignedBigInteger('processed_by')->nullable()->comment('Agent ayant traité le prêt');
            $table->unsignedBigInteger('returned_to')->nullable()->comment('Agent ayant reçu le retour');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('returned_to')->references('id')->on('users')->onDelete('set null');

            // Notes
            $table->text('notes')->nullable()->comment('Notes diverses');
            $table->text('internal_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index de performance
            $table->index('copy_id');
            $table->index('borrower_id');
            $table->index('status');
            $table->index('due_date');
            $table->index('loan_date');
            $table->index(['status', 'due_date'], 'idx_status_due');
            $table->index(['borrower_id', 'status'], 'idx_borrower_status');
        });

        // Table des réservations
        Schema::create('record_book_reservations', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('book_id')
                ->constrained('record_books')
                ->onDelete('cascade')
                ->comment('Livre réservé');

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Utilisateur ayant réservé');

            // Dates
            $table->date('reservation_date');
            $table->date('expiry_date')->nullable()->comment('Date limite pour retirer le livre');
            $table->date('pickup_date')->nullable()->comment('Date de retrait effectif');
            $table->date('cancellation_date')->nullable();

            // Statut
            $table->enum('status', ['pending', 'available', 'fulfilled', 'cancelled', 'expired'])->default('pending');

            // File d'attente
            $table->integer('queue_position')->default(0)->comment('Position dans la file d\'attente');
            $table->integer('original_queue_position')->nullable();

            // Exemplaire réservé
            $table->foreignId('copy_id')
                ->nullable()
                ->constrained('record_book_copies')
                ->onDelete('set null')
                ->comment('Exemplaire mis de côté');

            // Notifications
            $table->timestamp('notified_at')->nullable()->comment('Date de notification que le livre est disponible');
            $table->integer('notification_count')->default(0);
            $table->timestamp('last_notification_sent')->nullable();

            // Priorité
            $table->enum('priority', ['normal', 'high', 'urgent'])->default('normal');
            $table->boolean('is_vip_request')->default(false);

            // Localisation préférée
            $table->string('preferred_pickup_location', 200)->nullable();

            // Annulation
            $table->enum('cancellation_reason', [
                'user_request', 'timeout', 'book_unavailable', 'duplicate', 'other'
            ])->nullable();
            $table->text('cancellation_notes')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');

            // Conversion en prêt
            $table->unsignedBigInteger('loan_id')->nullable()->comment('Prêt créé à partir de cette réservation');
            $table->foreign('loan_id')->references('id')->on('record_book_loans')->onDelete('set null');

            // Notes
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();

            // Audit
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Index de performance
            $table->index('book_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('queue_position');
            $table->index('reservation_date');
            $table->index(['book_id', 'status', 'queue_position'], 'idx_book_status_queue');
            $table->index(['user_id', 'status'], 'idx_user_status');
        });

        // Add foreign key for current_loan_id in record_book_copies
        Schema::table('record_book_copies', function (Blueprint $table) {
            $table->foreign('current_loan_id')
                ->references('id')
                ->on('record_book_loans')
                ->onDelete('set null');
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
