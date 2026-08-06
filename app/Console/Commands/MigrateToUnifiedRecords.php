<?php

namespace App\Console\Commands;

use App\Models\Record;
use App\Models\RecordLevel;
use App\Models\RecordPhysical;
use App\Models\RecordStatus;
use App\Models\RecordType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2/3 — Bascule vers le modèle unifié `records` + `record_mediums`.
 *
 * Idempotente : rejouable sans doublon (détection via legacy_source/legacy_id).
 * Transactionnelle par lot.
 *
 * `RecordDigitalFolder`/`RecordDigitalDocument` ont été supprimés le 2026-08-06
 * (leurs 91 lignes historiques avaient déjà été migrées vers `records` avant
 * suppression, voir `legacy_source` = 'digital_folder'/'digital_document') :
 * cette commande ne migre donc plus que `record_physicals`, toujours actif.
 */
class MigrateToUnifiedRecords extends Command
{
    protected $signature = 'records:migrate-to-unified {--dry-run : Affiche ce qui serait migré sans rien écrire}';

    protected $description = 'Migre record_physicals vers le modèle unifié records + record_mediums';

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
            $this->replayParents();
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
    {
        $level = RecordLevel::find($levelId);

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

    private function migratePhysicals(): void
    {
        $this->info('1/3 record_physicals → records');

        RecordPhysical::query()->chunkById(500, function ($records) {
            foreach ($records as $source) {
                if ($this->alreadyMigrated('physical', $source->id)) {
                    continue;
                }

                $data = $this->mapPhysical($source);
                $record = $this->option('dry-run') ? null : Record::create($data);

                if (!$this->option('dry-run')) {
                    $this->map['physical:' . $source->id] = $record->id;

                    // Champs descriptifs ISAD(G) : MetadataDefinition système
                    // (`SystemMetadataDefinitionsSeeder`), stockées dans `metadata`
                    // plutôt qu'en colonnes directes sur `records` (2026-08-05).
                    $record->setMultipleMetadata($this->mapPhysicalIsadMetadata($source));
                    $record->save();
                }

                $this->syncPivots($source);
            }
        });
    }

    private const ISAD_FIELDS = [
        'biographical_history', 'archival_history', 'acquisition_source', 'content', 'appraisal',
        'accrual', 'arrangement', 'access_conditions', 'reproduction_conditions', 'language_material',
        'characteristic', 'finding_aids', 'location_original', 'location_copy', 'related_unit',
        'publication_note', 'note', 'archivist_note', 'rule_convention',
    ];

    private function mapPhysicalIsadMetadata(RecordPhysical $source): array
    {
        $metadata = [];

        foreach (self::ISAD_FIELDS as $field) {
            $metadata[$field] = $source->{$field};
        }

        return $metadata;
    }

    private function mapPhysical(RecordPhysical $source): array
    {
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

        $data['type_id'] = $source->level_id ? $this->typeIdForLevel($source->level_id) : null;

        return $data;
    }

    /**
     * Rejoue parent_id via la table de correspondance.
     */
    private function replayParents(): void
    {
        $this->info('2/3 parent_id (rejoué via record_migration_map)');

        if ($this->option('dry-run')) {
            return;
        }

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
    }

    /**
     * Phase 3 — record_mediums (support physique) si la table existe.
     */
    private function migrateMediums(): void
    {
        if (!Schema::hasTable('record_mediums')) {
            $this->warn('  record_mediums absent — Phase 3 à exécuter après sa migration');

            return;
        }

        $this->info('3/3 record_mediums');

        if ($this->option('dry-run')) {
            return;
        }

        $paperSupportId = $this->ensureSupport('Papier');

        RecordPhysical::query()->chunkById(500, function ($records) use ($paperSupportId) {
            foreach ($records as $source) {
                $recordId = $this->recordId('physical', $source->id);
                if (!$recordId) {
                    continue;
                }

                $this->upsertMedium($recordId, $paperSupportId, $source->id);
            }
        });
    }

    /**
     * Crée le support canonique s'il n'existe pas (par nom, insensible à la casse).
     */
    private function ensureSupport(string $name): int
    {
        $existing = DB::table('record_supports')
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->value('id');

        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('record_supports')->insertGetId([
            'name' => $name,
            'description' => 'Support canonique unifié',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function upsertMedium(int $recordId, int $supportId, int $legacyId, array $extra = []): void
    {
        $exists = DB::table('record_mediums')->where('record_id', $recordId)->where('legacy_id', $legacyId)->first();

        if ($exists) {
            DB::table('record_mediums')->where('id', $exists->id)->update(array_merge([
                'support_id' => $supportId,
                'is_principal' => true,
                'updated_at' => now(),
            ], $extra));

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

    /**
     * Repointe les pivots physiques vers les nouvelles notices (Phase 4, partie
     * "données"). Les pivots unifiés (record_keyword, record_thesaurus_concept)
     * doivent exister.
     */
    private function syncPivots(RecordPhysical $source): void
    {
        if ($this->option('dry-run')) {
            return;
        }

        $recordId = $this->recordId('physical', $source->id);
        if (!$recordId) {
            return;
        }

        $this->syncKeywordPivots($source, $recordId);
        $this->syncThesaurusPivots($source, $recordId);
    }

    private function syncKeywordPivots(RecordPhysical $source, int $recordId): void
    {
        if (!Schema::hasTable('record_keyword') || !Schema::hasTable('record_physical_keyword')) {
            return;
        }

        foreach (DB::table('record_physical_keyword')->where('record_id', $source->id)->get() as $pivot) {
            DB::table('record_keyword')->insertOrIgnore([
                'record_id' => $recordId,
                'keyword_id' => $pivot->keyword_id,
            ]);
        }
    }

    private function syncThesaurusPivots(RecordPhysical $source, int $recordId): void
    {
        if (!Schema::hasTable('record_thesaurus_concept') || !Schema::hasTable('record_physical_thesaurus_concept')) {
            return;
        }

        foreach (DB::table('record_physical_thesaurus_concept')->where('record_physical_id', $source->id)->get() as $pivot) {
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
            ]
        );

        $this->info('Total records : ' . Record::count());

        if (Schema::hasTable('record_mediums')) {
            $this->info('Total record_mediums : ' . DB::table('record_mediums')->count());
        }
    }
}
