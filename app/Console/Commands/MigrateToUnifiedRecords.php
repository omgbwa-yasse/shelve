<?php

namespace App\Console\Commands;

use App\Models\Record;
use App\Models\RecordDigitalDocument;
use App\Models\RecordDigitalFolder;
use App\Models\RecordLevel;
use App\Models\RecordPhysical;
use App\Models\RecordRelation;
use App\Models\RecordStatus;
use App\Models\RecordType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2/3 — Bascule vers le modèle unifié `records` + `record_mediums`.
 *
 * Idempotente : rejouable sans doublon (détection via legacy_source/legacy_id).
 * Transactionnelle par lot. Nettoyage de l'ancien schéma en Phase 7 uniquement.
 */
class MigrateToUnifiedRecords extends Command
{
    protected $signature = 'records:migrate-to-unified {--dry-run : Affiche ce qui serait migré sans rien écrire}';

    protected $description = 'Migre record_physicals / record_digital_folders / record_digital_documents vers le modèle unifié records + record_mediums';

    private array $map = [];

    private ?int $fallbackOrganisationId = null;

    public function handle(): int
    {
        if ($this->option('dry-run')) {
            $this->info('--- DRY RUN ---');
        }

        $this->loadExistingMap();

        DB::transaction(function () {
            $this->migratePhysicals();
            $this->migrateDigitalFolders();
            $this->migrateDigitalDocuments();
            $this->replayParents();
            $this->createVersionRelations();
            $this->migrateBridges();
            $this->migrateMediums();
        });

        $this->reportCounts();

        return self::SUCCESS;
    }

    private function loadExistingMap(): void
    {
        $rows = DB::table('records')
            ->whereNotNull('legacy_source')
            ->whereNotNull('legacy_id')
            ->get(['legacy_source', 'legacy_id', 'id']);

        foreach ($rows as $row) {
            $this->map[$row->legacy_source . ':' . $row->legacy_id] = $row->id;
        }
    }

    private function alreadyMigrated(string $source, int $id): bool
    {
        return isset($this->map[$source . ':' . $id]);
    }

    private function recordId(string $source, int $id): ?int
    {
        return $this->map[$source . ':' . $id] ?? null;
    }

    private function resolveStatusId(?string $status): ?int
    {
        if (!$status) {
            return null;
        }

        $match = match (strtolower($status)) {
            'active', 'published', 'final' => 'publié',
            'draft', 'brouillon' => 'brouillon',
            'archived', 'archive' => 'archivé',
            default => null,
        };

        if ($match) {
            $found = RecordStatus::whereRaw('LOWER(name) = ?', [$match])->first();
            if ($found) {
                return $found->id;
            }
        }

        return null;
    }

    private function fallbackStatusId(): ?int
    {
        return RecordStatus::query()->value('id');
    }

    private function levelIdByName(string $name): ?int
    {
        return RecordLevel::whereRaw('LOWER(name) = ?', [strtolower($name)])->value('id');
    }

    private function resolveOrganisationId(?int $id): int
    {
        if ($id) {
            return $id;
        }

        if ($this->fallbackOrganisationId === null) {
            $this->fallbackOrganisationId = DB::table('organisations')->value('id');
        }

        return $this->fallbackOrganisationId;
    }

    private function resolveCode(?string $code, string $source, int $id): string
    {
        if ($code && trim($code) !== '') {
            return $code;
        }

        $candidate = 'MIG-' . strtoupper(substr($source, 0, 3)) . '-' . $id;

        while (DB::table('records')->where('code', $candidate)->exists()) {
            $candidate .= '-x';
        }

        return $candidate;
    }

    private function typeIdForLevel(int $levelId): ?int
    {        $level = RecordLevel::find($levelId);

        if (!$level) {
            return null;
        }

        $code = strtolower($level->name);

        return match (true) {
            str_contains($code, 'fonds') => RecordType::where('code', 'FONDS')->value('id'),
            str_contains($code, 'pièce'), str_contains($code, 'piece'), str_contains($code, 'document') => RecordType::where('code', 'PAPER_RECORD')->value('id'),
            default => RecordType::where('code', 'PAPER_FOLDER')->value('id'),
        };
    }

    private function recordTypeIdFromLegacy(string $legacyType): ?int
    {
        return RecordType::where('legacy_type', $legacyType)->value('id');
    }

    private function migratePhysicals(): void
    {
        $this->info('1/7 record_physicals → records');

        RecordPhysical::query()->chunkById(500, function ($records) {
            foreach ($records as $source) {
                if ($this->alreadyMigrated('physical', $source->id)) {
                    continue;
                }

                $data = $this->mapPhysical($source);
                $record = $this->option('dry-run') ? null : Record::create($data);

                if (!$this->option('dry-run')) {
                    $this->map['physical:' . $source->id] = $record->id;
                }

                $this->syncPivots($source, 'physical');
            }
        });
    }

    private function mapPhysical(RecordPhysical $source): array
    {
        $isad = [
            'biographical_history', 'archival_history', 'acquisition_source', 'content', 'appraisal',
            'accrual', 'arrangement', 'access_conditions', 'reproduction_conditions', 'language_material',
            'characteristic', 'finding_aids', 'location_original', 'location_copy', 'related_unit',
            'publication_note', 'note', 'archivist_note', 'rule_convention',
        ];

        $data = [
            'code' => $this->resolveCode($source->code, 'physical', $source->id),
            'name' => $source->name,
            'level_id' => $source->level_id,
            'status_id' => $source->status_id ?? $this->fallbackStatusId(),
            'activity_id' => $source->activity_id,
            'parent_id' => null,
            'organisation_id' => $this->resolveOrganisationId($source->organisation_id),
            'creator_id' => $source->user_id,
            'access_level' => 'internal',
            'start_date' => $source->date_start,
            'end_date' => $source->date_end,
            'date_exact' => $source->date_exact,
            'date_format' => $source->date_format,
            'version_number' => 1,
            'is_current_version' => true,
            'legacy_source' => 'physical',
            'legacy_id' => $source->id,
        ];

        foreach ($isad as $field) {
            $data[$field] = $source->{$field};
        }

        $data['type_id'] = $source->level_id ? $this->typeIdForLevel($source->level_id) : null;

        return $data;
    }

    private function migrateDigitalFolders(): void
    {
        $this->info('2/7 record_digital_folders → records');

        RecordDigitalFolder::withTrashed()->chunkById(500, function ($folders) {
            foreach ($folders as $source) {
                if ($this->alreadyMigrated('digital_folder', $source->id)) {
                    continue;
                }

                $data = [
                    'code' => $this->resolveCode($source->code, 'digital_folder', $source->id),
                    'name' => $source->name,
                    'description' => $source->description,
                    'type_id' => $this->recordTypeIdFromLegacy('digital_folder_type:' . $source->type_id),
                    'level_id' => $this->levelIdByName('dossier') ?? RecordLevel::query()->min('id'),
                    'parent_id' => null,
                    'metadata' => $source->metadata,
                    'access_level' => $source->access_level ?? 'internal',
                    'status_id' => $this->resolveStatusId($source->status) ?? $this->fallbackStatusId(),
                    'requires_approval' => $source->requires_approval,
                    'approved_by' => $source->approved_by,
                    'approved_at' => $source->approved_at,
                    'creator_id' => $source->creator_id,
                    'organisation_id' => $this->resolveOrganisationId($source->organisation_id),
                    'assigned_to' => $source->assigned_to,
                    'start_date' => $source->start_date,
                    'end_date' => $source->end_date,
                    'version_number' => 1,
                    'is_current_version' => true,
                    'legacy_source' => 'digital_folder',
                    'legacy_id' => $source->id,
                ];

                $record = $this->option('dry-run') ? null : Record::create($data);

                if (!$this->option('dry-run')) {
                    $this->map['digital_folder:' . $source->id] = $record->id;
                }

                $this->syncPivots($source, 'digital_folder');
            }
        });
    }

    private function migrateDigitalDocuments(): void
    {
        $this->info('3/7 record_digital_documents → records (une notice par version)');

        RecordDigitalDocument::withTrashed()->chunkById(500, function ($documents) {
            foreach ($documents as $source) {
                if ($this->alreadyMigrated('digital_document', $source->id)) {
                    continue;
                }

                $data = [
                    'code' => $this->resolveCode($source->code, 'digital_document', $source->id),
                    'name' => $source->name,
                    'description' => $source->description,
                    'type_id' => $this->recordTypeIdFromLegacy('digital_document_type:' . $source->type_id),
                    'level_id' => $this->levelIdByName('pièce') ?? $this->levelIdByName('piece') ?? RecordLevel::query()->min('id'),
                    'parent_id' => null,
                    'metadata' => $source->metadata,
                    'access_level' => $source->access_level ?? 'internal',
                    'status_id' => $this->resolveStatusId($source->status) ?? $this->fallbackStatusId(),
                    'requires_approval' => $source->requires_approval,
                    'approved_by' => $source->approved_by,
                    'approved_at' => $source->approved_at,
                    'creator_id' => $source->creator_id,
                    'organisation_id' => $this->resolveOrganisationId($source->organisation_id),
                    'assigned_to' => $source->assigned_to,
                    'start_date' => $source->document_date,
                    'date_exact' => $source->document_date,
                    'version_number' => $source->version_number ?? 1,
                    'is_current_version' => $source->is_current_version ?? true,
                    'legacy_source' => 'digital_document',
                    'legacy_id' => $source->id,
                ];

                $record = $this->option('dry-run') ? null : Record::create($data);

                if (!$this->option('dry-run')) {
                    $this->map['digital_document:' . $source->id] = $record->id;
                }

                $this->syncPivots($source, 'digital_document');
            }
        });
    }

    /**
     * Rejoue parent_id via la table de correspondance.
     */
    private function replayParents(): void
    {
        $this->info('4/7 parent_id (rejoué via record_migration_map)');

        if ($this->option('dry-run')) {
            return;
        }

        // Physiques : parent physique
        foreach ($this->map as $key => $recordId) {
            [$source, $id] = explode(':', $key, 2);
            $id = (int) $id;

            if ($source !== 'physical') {
                continue;
            }

            $physical = RecordPhysical::query()->find($id);
            if (!$physical || !$physical->parent_id) {
                continue;
            }

            $parentRecordId = $this->recordId('physical', $physical->parent_id);
            if ($parentRecordId) {
                DB::table('records')->where('id', $recordId)->update(['parent_id' => $parentRecordId]);
            }
        }

        // Dossiers numériques : parent dossier numérique
        foreach ($this->map as $key => $recordId) {
            [$source, $id] = explode(':', $key, 2);
            $id = (int) $id;

            if ($source !== 'digital_folder') {
                continue;
            }

            $folder = RecordDigitalFolder::withTrashed()->find($id);
            if (!$folder || !$folder->parent_id) {
                continue;
            }

            $parentRecordId = $this->recordId('digital_folder', $folder->parent_id);
            if ($parentRecordId) {
                DB::table('records')->where('id', $recordId)->update(['parent_id' => $parentRecordId]);
            }
        }

        // Documents numériques : parent = dossier numérique du document (idem pour toutes les versions)
        $rows = DB::table('record_digital_documents')->whereNotNull('folder_id')->get(['id', 'folder_id']);

        foreach ($rows as $row) {
            $recordId = $this->recordId('digital_document', $row->id);
            if (!$recordId) {
                continue;
            }

            $parentRecordId = $this->recordId('digital_folder', $row->folder_id);
            if ($parentRecordId) {
                DB::table('records')->where('id', $recordId)->update(['parent_id' => $parentRecordId]);
            }
        }
    }

    /**
     * Chaîne les versions (record_relations type=version_of), v(n) → v(n-1).
     */
    private function createVersionRelations(): void
    {
        $this->info('5/7 record_relations (version_of)');

        if ($this->option('dry-run')) {
            return;
        }

        // Regroupe les documents par racine de version (parent_version_id nul = racine)
        $roots = DB::table('record_digital_documents')->whereNull('parent_version_id')->get(['id']);

        foreach ($roots as $root) {
            $family = DB::table('record_digital_documents')
                ->where(function ($q) use ($root) {
                    $q->where('id', $root->id)->orWhere('parent_version_id', $root->id);
                })
                ->orderBy('version_number')
                ->get();

            $previousRecordId = null;

            foreach ($family as $version) {
                $recordId = $this->recordId('digital_document', $version->id);
                if (!$recordId) {
                    continue;
                }

                if ($previousRecordId !== null) {
                    RecordRelation::firstOrCreate([
                        'source_id' => $recordId,
                        'target_id' => $previousRecordId,
                        'type' => RecordRelation::TYPE_VERSION_OF,
                    ]);
                }

                $previousRecordId = $recordId;
            }
        }
    }

    /**
     * Ponts physique↔numérique existants (transferred_to_record_id côté numérique +
     * linked_digital_metadata côté physique) : fusionnés en Phase 3 par l'ajout du second
     * support. Ici on ne fait que tracer.
     */
    private function migrateBridges(): void
    {
        $this->info('6/7 ponts physique↔numérique (tracé — fusion supports en Phase 3)');

        $digitalWithTransfer = DB::table('record_digital_documents')->whereNotNull('transferred_to_record_id')->count()
            + DB::table('record_digital_folders')->whereNotNull('transferred_to_record_id')->count();
        $physicalWithLinked = RecordPhysical::query()->whereNotNull('linked_digital_metadata')->count();

        $this->info("  ponts numériques → physique : {$digitalWithTransfer}");
        $this->info("  physiques avec linked_digital_metadata : {$physicalWithLinked}");
    }

    /**
     * Phase 3 — record_mediums (support physique OU numérique) si la table existe.
     */
    private function migrateMediums(): void
    {
        if (!Schema::hasTable('record_mediums')) {
            $this->warn('  record_mediums absent — Phase 3 à exécuter après sa migration');

            return;
        }

        $this->info('7/7 record_mediums');

        if ($this->option('dry-run')) {
            return;
        }

        $digitalSupportId = DB::table('record_supports')->whereRaw('LOWER(name) = ?', ['numérique'])->value('id')
            ?? DB::table('record_supports')->value('id');
        $paperSupportId = DB::table('record_supports')->whereRaw('LOWER(name) = ?', ['papier'])->value('id')
            ?? $digitalSupportId;

        // 7.1 Physiques → support papier
        RecordPhysical::query()->chunkById(500, function ($records) use ($paperSupportId) {
            foreach ($records as $source) {
                $recordId = $this->recordId('physical', $source->id);
                if (!$recordId) {
                    continue;
                }

                $this->createMedium($recordId, $paperSupportId, $source->id);
            }
        });

        // 7.2 Documents numériques → support numérique (état fichier depuis record_digital_documents)
        $rows = DB::table('record_digital_documents')->get([
            'id', 'attachment_id', 'checked_out_by', 'checked_out_at',
            'signature_status', 'signed_by', 'signed_at', 'signature_data',
        ]);

        foreach ($rows as $row) {
            $recordId = $this->recordId('digital_document', $row->id);
            if (!$recordId) {
                continue;
            }

            $status = ($row->signature_status && $row->signature_status !== 'unsigned') ? 'final' : 'draft';

            $this->createMedium($recordId, $digitalSupportId, $row->id, [
                'attachment_id' => $row->attachment_id,
                'status' => $status,
                'checked_out_by' => $row->checked_out_by,
                'checked_out_at' => $row->checked_out_at,
                'signature_status' => $row->signature_status ?? 'unsigned',
                'signed_by' => $row->signed_by,
                'signed_at' => $row->signed_at,
                'signature_data' => $row->signature_data,
            ]);
        }

        // 7.3 Fusions physique↔numérique (document numérisé) : une notice, deux supports.
        // Le second support est créé par addMedium côté applicatif ; ici on fusionne les paires
        // dont la notice numérique a déjà été déclassée/transférée vers une notice physique.
        $this->mergeTransferredPairs($digitalSupportId);
    }

    private function createMedium(int $recordId, int $supportId, int $legacyId, array $extra = []): void
    {
        $exists = DB::table('record_mediums')
            ->where('record_id', $recordId)
            ->where('legacy_id', $legacyId)
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('record_mediums')->insert(array_merge([
            'record_id' => $recordId,
            'support_id' => $supportId,
            'is_principal' => true,
            'legacy_id' => $legacyId,
            'created_at' => now(),
            'updated_at' => now(),
        ], $extra));
    }

    private function mergeTransferredPairs(int $digitalSupportId): void
    {
        // Le pont : record_digital_documents.transferred_to_record_id → record_physicals.
        // On rattache le support numérique à la notice physique (une notice, deux supports).
        $rows = DB::table('record_digital_documents')
            ->whereNotNull('transferred_to_record_id')
            ->get(['id', 'transferred_to_record_id']);

        foreach ($rows as $row) {
            $digitalRecordId = $this->recordId('digital_document', $row->id);
            $physicalRecordId = $this->recordId('physical', $row->transferred_to_record_id);

            if (!$physicalRecordId || !$digitalRecordId) {
                continue;
            }

            // Le medium numérique de la notice numérique est déplacé sur la notice physique.
            DB::table('record_mediums')
                ->where('record_id', $digitalRecordId)
                ->where('support_id', $digitalSupportId)
                ->update(['record_id' => $physicalRecordId, 'is_principal' => false]);

            // La notice numérique devient une version-fille (obsolète) de la notice physique.
            RecordRelation::firstOrCreate([
                'source_id' => $digitalRecordId,
                'target_id' => $physicalRecordId,
                'type' => RecordRelation::TYPE_VERSION_OF,
            ]);
        }
    }

    /**
     * Repointe les pivots physiques/numériques vers les nouvelles notices (Phase 4, partie "données").
     * Les pivots unifiés (record_keyword, record_thesaurus_concept, record_author) doivent exister.
     */
    private function syncPivots($source, string $sourceType): void
    {
        if ($this->option('dry-run')) {
            return;
        }

        $recordId = $this->recordId($sourceType, $source->id);
        if (!$recordId) {
            return;
        }

        $this->syncKeywordPivots($source, $sourceType, $recordId);
        $this->syncThesaurusPivots($source, $sourceType, $recordId);
    }

    private function syncKeywordPivots($source, string $sourceType, int $recordId): void
    {
        $table = match ($sourceType) {
            'physical' => 'record_physical_keyword',
            'digital_folder' => 'record_digital_folder_keyword',
            'digital_document' => 'record_digital_document_keyword',
            default => null,
        };

        $column = match ($sourceType) {
            'physical' => 'record_id',
            'digital_folder' => 'folder_id',
            'digital_document' => 'document_id',
            default => null,
        };

        if (!$table || !Schema::hasTable('record_keyword') || !Schema::hasTable($table)) {
            return;
        }

        foreach (DB::table($table)->where($column, $source->id)->get() as $pivot) {
            DB::table('record_keyword')->insertOrIgnore([
                'record_id' => $recordId,
                'keyword_id' => $pivot->keyword_id,
            ]);
        }
    }

    private function syncThesaurusPivots($source, string $sourceType, int $recordId): void
    {
        $table = match ($sourceType) {
            'physical' => 'record_physical_thesaurus_concept',
            'digital_folder' => 'record_digital_folder_thesaurus_concept',
            'digital_document' => 'record_digital_document_thesaurus_concept',
            default => null,
        };

        $column = match ($sourceType) {
            'physical' => 'record_physical_id',
            'digital_folder' => 'folder_id',
            'digital_document' => 'document_id',
            default => null,
        };

        if (!$table || !Schema::hasTable('record_thesaurus_concept') || !Schema::hasTable($table)) {
            return;
        }

        foreach (DB::table($table)->where($column, $source->id)->get() as $pivot) {
            DB::table('record_thesaurus_concept')->insertOrIgnore([
                'record_id' => $recordId,
                'concept_id' => $pivot->concept_id,
                'weight' => $pivot->weight ?? null,
                'context' => $pivot->context ?? null,
                'extraction_note' => $pivot->extraction_note ?? null,
            ]);
        }
    }

    private function reportCounts(): void
    {
        $this->newLine();

        if ($this->option('dry-run')) {
            return;
        }

        $this->table(
            ['Source', 'Compteur', '→ records'],
            [
                ['record_physicals', RecordPhysical::query()->count(), Record::where('legacy_source', 'physical')->count()],
                ['record_digital_folders', RecordDigitalFolder::withTrashed()->count(), Record::where('legacy_source', 'digital_folder')->count()],
                ['record_digital_documents', RecordDigitalDocument::withTrashed()->count(), Record::where('legacy_source', 'digital_document')->count()],
            ]
        );

        $this->info('Total records : ' . Record::count());

        if (Schema::hasTable('record_mediums')) {
            $this->info('Total record_mediums : ' . DB::table('record_mediums')->count());
        }
    }
}
