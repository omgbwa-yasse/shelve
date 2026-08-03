<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Filet de rattrapage : sur une base récente, `reservations.return_date` et
     * `return_effective` sont déjà créées par 2024_11_02_000006. Cette migration
     * ne les ajoute donc que si elles manquent (bases plus anciennes).
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'return_date')) {
                $table->date('return_date')->nullable()->after('communication_id');
            }
            if (!Schema::hasColumn('reservations', 'return_effective')) {
                $table->date('return_effective')->nullable()->after('return_date');
            }
        });
    }

    /**
     * Ne rien supprimer : ces colonnes appartiennent à 2024_11_02_000006, qui les
     * crée avec la table. Les retirer ici cassait le rollback (et le down() de la
     * migration propriétaire).
     */
    public function down(): void
    {
        // no-op volontaire
    }
};
