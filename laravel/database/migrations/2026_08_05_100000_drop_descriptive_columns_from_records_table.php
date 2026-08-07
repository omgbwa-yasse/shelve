<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Les ~27 colonnes descriptives de `records` (ISAD(G) + descriptifs non-ISAD)
 * deviennent des `metadata_definitions` (is_system=true), attachées par
 * `RecordTypeMetadataProfile` et stockées dans `records.metadata` (JSON, déjà
 * présent) — voir SystemMetadataDefinitionsSeeder, qui doit avoir tourné avant
 * cette migration en environnement disposant de données à préserver. Vérifié le
 * 2026-08-05 : aucune des 132 notices existantes n'avait de valeur non vide dans
 * l'une de ces colonnes — pas de script de migration de données nécessaire.
 */
return new class extends Migration
{
    private array $columns = [
        'biographical_history', 'archival_history', 'acquisition_source', 'content',
        'appraisal', 'accrual', 'arrangement', 'access_conditions',
        'reproduction_conditions', 'language_material', 'characteristic',
        'finding_aids', 'location_original', 'location_copy', 'related_unit',
        'publication_note', 'note', 'archivist_note', 'rule_convention',
        'extent', 'category_precision', 'table_of_contents', 'quantity',
        'dimension', 'publisher', 'sort_value', 'geographic_scope',
    ];

    public function up(): void
    {
        Schema::table('records', function (Blueprint $table) {
            $existing = array_filter($this->columns, fn ($c) => Schema::hasColumn('records', $c));

            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }

    public function down(): void
    {
        Schema::table('records', function (Blueprint $table) {
            foreach ($this->columns as $column) {
                if (!Schema::hasColumn('records', $column)) {
                    $table->text($column)->nullable();
                }
            }
        });
    }
};
