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
        Schema::table('mails', function (Blueprint $table) {
            $table->foreignId('external_sender_id')->nullable()
                  ->constrained('external_contacts')
                  ->nullOnDelete();

            $table->foreignId('external_recipient_id')->nullable()
                  ->constrained('external_contacts')
                  ->nullOnDelete();

            $table->string('sender_type')->nullable()
                  ->comment('Type de l\'expéditeur: user, organisation, external');

            $table->string('recipient_type')->nullable()
                  ->comment('Type du destinataire: user, organisation, external');

            // Champs pour le suivi des courriers externes
            $table->dateTime('sent_at')->nullable()->comment('Date d\'envoi effectif');
            $table->dateTime('received_at')->nullable()->comment('Date de réception confirmée');
            $table->string('delivery_method')->nullable()->comment('Méthode d\'envoi/réception: email, courrier, en main propre, etc.');
            $table->string('tracking_number')->nullable()->comment('Numéro de suivi pour les courriers postaux');
            $table->boolean('receipt_confirmed')->default(false)->comment('Confirmation de réception');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mails', function (Blueprint $table) {
            $table->dropForeign(['external_sender_id']);
            $table->dropForeign(['external_recipient_id']);
            $table->dropColumn([
                'external_sender_id',
                'external_recipient_id',
                'sender_type',
                'recipient_type',
                'sent_at',
                'received_at',
                'delivery_method',
                'tracking_number',
                'receipt_confirmed'
            ]);
        });
    }
};
