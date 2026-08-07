<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Suppression de l'ancienne architecture RecordDigitalFolder/RecordDigitalDocument
 * (2026-08-06), remplacée par le modèle unifié `records` (voir `App\Models\Record`,
 * `2026_08_04_000003_create_unified_records_table.php`).
 *
 * Préalable vérifié avant suppression : les 45 dossiers et 46 documents numériques
 * existants ont été migrés vers `records` via `records:migrate-to-unified`
 * (`legacy_source` = 'digital_folder'/'digital_document') — voir
 * `App\Console\Commands\MigrateToUnifiedRecords`.
 *
 * Emporte avec elle : les pivots dolly/mots-clés/thésaurus dédiés, les types
 * (`record_digital_folder_types`/`record_digital_document_types`) et leurs
 * profils de métadonnées, ainsi que `workplace_folders`/`workplace_documents`
 * (partage de fichiers dans un Workplace, qui n'a plus de cible sans ces
 * tables — voir `WorkplaceContentController`, supprimé avec ce chantier).
 *
 * Aucune contrainte FOREIGN KEY ne référence ces tables (vérifié via
 * information_schema avant écriture) — l'ordre ci-dessous (pivots/enfants
 * avant tables principales) est une précaution, pas une nécessité stricte.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('dolly_digital_documents');
        Schema::dropIfExists('dolly_digital_folders');
        Schema::dropIfExists('record_digital_document_keyword');
        Schema::dropIfExists('record_digital_document_thesaurus_concept');
        Schema::dropIfExists('record_digital_folder_keyword');
        Schema::dropIfExists('record_digital_folder_thesaurus_concept');
        Schema::dropIfExists('workplace_folders');
        Schema::dropIfExists('workplace_documents');

        Schema::dropIfExists('record_digital_documents');
        Schema::dropIfExists('record_digital_folders');

        Schema::dropIfExists('record_digital_document_metadata_profiles');
        Schema::dropIfExists('record_digital_folder_metadata_profiles');
        Schema::dropIfExists('record_digital_document_types');
        Schema::dropIfExists('record_digital_folder_types');
    }

    /**
     * Pas de rollback structurel : recréer les tables ne restaurerait pas les
     * données (déjà migrées vers `records`) ni le code (modèles/contrôleurs
     * supprimés dans le même chantier). En cas de besoin, restaurer depuis une
     * sauvegarde de base antérieure à cette migration.
     */
    public function down(): void
    {
        // Volontairement vide — voir docblock.
    }
};
