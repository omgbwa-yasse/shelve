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
