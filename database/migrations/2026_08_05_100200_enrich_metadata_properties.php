<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Étapes 3, 4 et 5 — Alignement Constellio.
 *
 * - metadata_definitions : propriétés d'affichage et de validation enrichies
 *   (sortable, highlightable, autocomplete, unique, input_mask, max_length),
 *   métadonnées copiées (copy_source_type/copy_source_field) et calculées
 *   (computed_template).
 * - record_type_metadata_profiles : groupement en onglets (`group`) et sécurité
 *   par rôle (`restricted_to_roles`).
 * - reference_values : propriétés spécifiques par domaine (`extra_attributes`).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('metadata_definitions', function (Blueprint $table) {
            if (! Schema::hasColumn('metadata_definitions', 'sortable')) {
                $table->boolean('sortable')->default(false)->after('searchable');
            }
            if (! Schema::hasColumn('metadata_definitions', 'highlightable')) {
                $table->boolean('highlightable')->default(false)->after('sortable');
            }
            if (! Schema::hasColumn('metadata_definitions', 'autocomplete')) {
                $table->boolean('autocomplete')->default(false)->after('highlightable');
            }
            if (! Schema::hasColumn('metadata_definitions', 'unique')) {
                $table->boolean('unique')->default(false)->after('autocomplete');
            }
            if (! Schema::hasColumn('metadata_definitions', 'input_mask')) {
                $table->string('input_mask', 100)->nullable()->after('unique');
            }
            if (! Schema::hasColumn('metadata_definitions', 'max_length')) {
                $table->unsignedInteger('max_length')->nullable()->after('input_mask');
            }
            if (! Schema::hasColumn('metadata_definitions', 'copy_source_type')) {
                $table->string('copy_source_type', 30)->nullable()->after('max_length');
            }
            if (! Schema::hasColumn('metadata_definitions', 'copy_source_field')) {
                $table->string('copy_source_field', 100)->nullable()->after('copy_source_type');
            }
            if (! Schema::hasColumn('metadata_definitions', 'computed_template')) {
                $table->string('computed_template', 255)->nullable()->after('copy_source_field');
            }
        });

        Schema::table('record_type_metadata_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('record_type_metadata_profiles', 'group')) {
                $table->string('group', 100)->nullable()->after('validation_rules');
            }
            if (! Schema::hasColumn('record_type_metadata_profiles', 'restricted_to_roles')) {
                $table->json('restricted_to_roles')->nullable()->after('group');
            }
        });

        Schema::table('reference_values', function (Blueprint $table) {
            if (! Schema::hasColumn('reference_values', 'extra_attributes')) {
                $table->json('extra_attributes')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('metadata_definitions', function (Blueprint $table) {
            foreach ([
                'sortable', 'highlightable', 'autocomplete', 'unique', 'input_mask',
                'max_length', 'copy_source_type', 'copy_source_field', 'computed_template',
            ] as $column) {
                if (Schema::hasColumn('metadata_definitions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('record_type_metadata_profiles', function (Blueprint $table) {
            foreach (['group', 'restricted_to_roles'] as $column) {
                if (Schema::hasColumn('record_type_metadata_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('reference_values', function (Blueprint $table) {
            if (Schema::hasColumn('reference_values', 'extra_attributes')) {
                $table->dropColumn('extra_attributes');
            }
        });
    }
};
