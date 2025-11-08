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
        Schema::create('record_book_reservations', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->unsignedBigInteger('book_id');
            $table->foreign('book_id')->references('id')->on('record_books')->onDelete('cascade');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

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
            $table->unsignedBigInteger('copy_id')->nullable()->comment('Exemplaire mis de côté');
            $table->foreign('copy_id')->references('id')->on('record_book_copies')->onDelete('set null');

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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_book_reservations');
    }
};
