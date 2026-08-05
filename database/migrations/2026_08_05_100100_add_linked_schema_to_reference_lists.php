<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Étape 2 — Alignement Constellio : un domaine de valeurs peut désormais référencer
 * le schéma (RecordType) auquel il est associé (`linked_schema_id`), en complément
 * de la relation inverse (MetadataDefinition.reference_list_id).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reference_lists', function (Blueprint $table) {
            if (! Schema::hasColumn('reference_lists', 'linked_schema_id')) {
                $table->unsignedBigInteger('linked_schema_id')->nullable()->after('description');
                $table->foreign('linked_schema_id')
                    ->references('id')->on('record_types')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reference_lists', function (Blueprint $table) {
            if (Schema::hasColumn('reference_lists', 'linked_schema_id')) {
                $table->dropForeign(['linked_schema_id']);
                $table->dropColumn('linked_schema_id');
            }
        });
    }
};
