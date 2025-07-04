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
            // Ajout des champs pour les relations avec les entités externes
            $table->foreignId('external_sender_id')->nullable()->after('sender_organisation_id');
            $table->foreignId('external_sender_organization_id')->nullable()->after('external_sender_id');
            $table->foreignId('external_recipient_id')->nullable()->after('recipient_organisation_id');
            $table->foreignId('external_recipient_organization_id')->nullable()->after('external_recipient_id');

            // Ajout des colonnes pour le type d'expéditeur et de destinataire
            $table->string('sender_type')->nullable()->after('sender_organisation_id')
                  ->comment('Type d\'expéditeur: user, organisation, external_contact, external_organization');
            $table->string('recipient_type')->nullable()->after('recipient_organisation_id')
                  ->comment('Type de destinataire: user, organisation, external_contact, external_organization');

            // Ajout des contraintes de clé étrangère
            $table->foreign('external_sender_id')->references('id')->on('external_contacts')->onDelete('set null');
            $table->foreign('external_sender_organization_id')->references('id')->on('external_organizations')->onDelete('set null');
            $table->foreign('external_recipient_id')->references('id')->on('external_contacts')->onDelete('set null');
            $table->foreign('external_recipient_organization_id')->references('id')->on('external_organizations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mails', function (Blueprint $table) {
            // Suppression des contraintes de clé étrangère
            $table->dropForeign(['external_sender_id']);
            $table->dropForeign(['external_sender_organization_id']);
            $table->dropForeign(['external_recipient_id']);
            $table->dropForeign(['external_recipient_organization_id']);

            // Suppression des colonnes
            $table->dropColumn('external_sender_id');
            $table->dropColumn('external_sender_organization_id');
            $table->dropColumn('external_recipient_id');
            $table->dropColumn('external_recipient_organization_id');
            $table->dropColumn('sender_type');
            $table->dropColumn('recipient_type');
        });
    }
};
