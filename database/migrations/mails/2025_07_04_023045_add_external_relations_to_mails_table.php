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
            // Ajout uniquement des champs pour les organisations externes
            if (!Schema::hasColumn('mails', 'external_sender_organization_id')) {
                $table->foreignId('external_sender_organization_id')->nullable()->after('external_sender_id');
            }

            if (!Schema::hasColumn('mails', 'external_recipient_organization_id')) {
                $table->foreignId('external_recipient_organization_id')->nullable()->after('external_recipient_id');
            }

            // Pas besoin d'ajouter sender_type et recipient_type car ils existent déjà

            // Ajout des contraintes de clé étrangère pour les nouvelles colonnes uniquement
            if (Schema::hasColumn('mails', 'external_sender_organization_id')) {
                $table->foreign('external_sender_organization_id')->references('id')->on('external_organizations')->onDelete('set null');
            }

            if (Schema::hasColumn('mails', 'external_recipient_organization_id')) {
                $table->foreign('external_recipient_organization_id')->references('id')->on('external_organizations')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mails', function (Blueprint $table) {
            // Suppression des contraintes de clé étrangère uniquement pour les colonnes ajoutées par cette migration
            if (Schema::hasColumn('mails', 'external_sender_organization_id')) {
                $table->dropForeign(['external_sender_organization_id']);
                $table->dropColumn('external_sender_organization_id');
            }

            if (Schema::hasColumn('mails', 'external_recipient_organization_id')) {
                $table->dropForeign(['external_recipient_organization_id']);
                $table->dropColumn('external_recipient_organization_id');
            }

            // NB : sender_type / recipient_type appartiennent à la migration
            // 2025_07_04_022549 (cf. commentaire du up()) — les supprimer ici
            // cassait le rollback et le down() de cette autre migration.
        });
    }
};
