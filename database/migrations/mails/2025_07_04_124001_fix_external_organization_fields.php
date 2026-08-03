<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Filet de rattrapage : les colonnes external_*_organization_id appartiennent à
     * 2025_07_04_023045. On ne les recrée ici que si elles manquent (bases dont la
     * migration précédente a échoué en cours de route).
     */
    public function up(): void
    {
        Schema::table('mails', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('mails', 'external_sender_organization_id')) {
                $table->foreignId('external_sender_organization_id')->nullable();
                $table->foreign('external_sender_organization_id')
                      ->references('id')
                      ->on('external_organizations')
                      ->onDelete('set null');
            }

            if (!Schema::hasColumn('mails', 'external_recipient_organization_id')) {
                $table->foreignId('external_recipient_organization_id')->nullable();
                $table->foreign('external_recipient_organization_id')
                      ->references('id')
                      ->on('external_organizations')
                      ->onDelete('set null');
            }
        });
    }

    /**
     * Ne rien supprimer : cette migration ne fait que rattraper des colonnes
     * appartenant à 2025_07_04_023045, dont c'est le `down()` qui les retire.
     */
    public function down(): void
    {
        // no-op volontaire
    }
};
