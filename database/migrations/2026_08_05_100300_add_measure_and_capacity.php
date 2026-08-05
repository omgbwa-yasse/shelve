<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Étape 6 — Alignement Constellio : papier vs numérique.
 *
 * - record_mediums : `linear_measure_cm` (mesure linéaire en centimètres).
 * - records : `linear_measure_cm` (mesure linéaire agrégée de la notice).
 * - containers : `capacity_cm` (capacité linéaire du contenant) → permet le
 *   calcul d'espace restant à l'affichage.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('record_mediums', function (Blueprint $table) {
            if (! Schema::hasColumn('record_mediums', 'linear_measure_cm')) {
                $table->decimal('linear_measure_cm', 10, 2)->nullable()->after('copy_code');
            }
        });

        Schema::table('records', function (Blueprint $table) {
            if (! Schema::hasColumn('records', 'linear_measure_cm')) {
                $table->decimal('linear_measure_cm', 10, 2)->nullable()->after('metadata');
            }
        });

        Schema::table('containers', function (Blueprint $table) {
            if (! Schema::hasColumn('containers', 'capacity_cm')) {
                $table->decimal('capacity_cm', 10, 2)->nullable()->after('property_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('record_mediums', function (Blueprint $table) {
            if (Schema::hasColumn('record_mediums', 'linear_measure_cm')) {
                $table->dropColumn('linear_measure_cm');
            }
        });

        Schema::table('records', function (Blueprint $table) {
            if (Schema::hasColumn('records', 'linear_measure_cm')) {
                $table->dropColumn('linear_measure_cm');
            }
        });

        Schema::table('containers', function (Blueprint $table) {
            if (Schema::hasColumn('containers', 'capacity_cm')) {
                $table->dropColumn('capacity_cm');
            }
        });
    }
};
