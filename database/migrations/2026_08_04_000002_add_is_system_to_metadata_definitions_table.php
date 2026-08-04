<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 1 — Distinction système / personnalisé (alignement MetadonneeProfilMO.personnalisee).
     *
     * is_system = true  : champ standard livré avec l'installation — non supprimable depuis l'UI,
     *                     seul visible/sort_order restent modifiables via le profil.
     * is_system = false : champ ajouté par un administrateur métier — supprimable, éditable librement.
     */
    public function up(): void
    {
        Schema::table('metadata_definitions', function (Blueprint $table) {
            $table->boolean('is_system')->default(false)->after('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('metadata_definitions', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });
    }
};
