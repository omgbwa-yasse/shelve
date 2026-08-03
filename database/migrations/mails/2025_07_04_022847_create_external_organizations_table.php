<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ATTENTION — le nom de ce fichier est trompeur (conservé tel quel : il est
     * déjà enregistré en production, le renommer le ferait rejouer).
     *
     * La table `external_organizations` est créée par 2025_07_04_022444.
     * Cette migration-ci ne fait que la COMPLÉTER (colonnes legal_form, etc.),
     * ou la créer en dernier recours si elle manque.
     */
    public function up(): void
    {
        if (Schema::hasTable('external_organizations')) {
            // Ajouter les colonnes manquantes si la table existe déjà
            Schema::table('external_organizations', function (Blueprint $table) {
                // Ajout des colonnes qui existent dans la deuxième migration mais pas dans la première
                if (!Schema::hasColumn('external_organizations', 'legal_form')) {
                    $table->string('legal_form')->nullable()->comment('Forme juridique: SARL, SA, etc.');
                }

                if (!Schema::hasColumn('external_organizations', 'city')) {
                    $table->string('city')->nullable();
                }

                if (!Schema::hasColumn('external_organizations', 'postal_code')) {
                    $table->string('postal_code')->nullable();
                }

                if (!Schema::hasColumn('external_organizations', 'country')) {
                    $table->string('country')->nullable()->default('France');
                }

                // Ajout d'index supplémentaire pour la colonne city
                $table->index('city');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // On ne supprime pas la table car elle a été créée par une autre migration
        // On supprime seulement les colonnes ajoutées
        if (Schema::hasTable('external_organizations')) {
            Schema::table('external_organizations', function (Blueprint $table) {
                $table->dropColumn(['legal_form', 'city', 'postal_code', 'country']);
            });
        }
    }
};
