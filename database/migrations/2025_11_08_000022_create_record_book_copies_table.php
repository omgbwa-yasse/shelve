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
        Schema::create('record_book_copies', function (Blueprint $table) {
            $table->id();

            // Relation avec le livre
            $table->unsignedBigInteger('book_id');
            $table->foreign('book_id')->references('id')->on('record_books')->onDelete('cascade');

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
            $table->unsignedBigInteger('current_loan_id')->nullable();
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_book_copies');
    }
};
