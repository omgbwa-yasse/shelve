<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 2 — Relations entre notices (remplace les 10 tables Relation*FicheDoc d'IntelliGID).
     *
     * Une seule table, stockée dans un seul sens (source -> target). Le sens inverse s'obtient
     * par requête sur target_id (index dédié). Les 10 types d'IntelliGID (EstVersionDe,
     * APourVersion, Remplace, EstRemplacePar, RefereA, EstReferPar, Requiert, EstRequisPar,
     * APourPartie, SeConformeA) sont portés par le champ `type`.
     */
    public function up(): void
    {
        Schema::create('record_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('records')->cascadeOnDelete();
            $table->foreignId('target_id')->constrained('records')->cascadeOnDelete();
            $table->string('type', 30); // version_of | replaces | refers_to | requires | has_part | conforms_to
            $table->timestamps();
            $table->unique(['source_id', 'target_id', 'type']);
            $table->index(['target_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_relations');
    }
};
