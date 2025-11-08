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
        Schema::create('record_book_loans', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->unsignedBigInteger('copy_id');
            $table->foreign('copy_id')->references('id')->on('record_book_copies')->onDelete('cascade');

            $table->unsignedBigInteger('borrower_id');
            $table->foreign('borrower_id')->references('id')->on('users')->onDelete('cascade');

            // Dates du prêt
            $table->date('loan_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->timestamp('actual_return_time')->nullable();

            // Statut
            $table->enum('status', ['active', 'returned', 'overdue', 'renewed', 'lost', 'cancelled'])->default('active');

            // Renouvellements
            $table->integer('renewal_count')->default(0);
            $table->integer('max_renewals')->default(3);
            $table->date('last_renewal_date')->nullable();

            // Pénalités
            $table->decimal('late_fee', 10, 2)->default(0);
            $table->decimal('damage_fee', 10, 2)->default(0);
            $table->decimal('total_fee', 10, 2)->default(0);
            $table->boolean('fee_paid')->default(false);
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
            $table->text('notes')->nullable();
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_book_loans');
    }
};
