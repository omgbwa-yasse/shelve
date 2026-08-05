<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 4 — Repointer les dépendants vers `records`.
     *
     * - Crée les pivots unifiés (record_author, record_keyword, record_thesaurus_concept,
     *   dolly_records) et backfill depuis les pivots legacy (les anciennes tables restent
     *   jusqu'à la Phase 7).
     * - Renomme record_physical_id → record_id sur declassement_records, record_reactivations,
     *   record_physical_attachment et repointe la FK vers `records`.
     * - Ajoute les colonnes support/agrégation à declassement_lists + table
     *   declassement_containers (ContenantListeDeclassement).
     */
    public function up(): void
    {
        $this->createUnifiedPivots();
        $this->backfillPivots();
        $this->repointColumnForeignKeys();
        $this->declassementAdditions();
    }

    private function createUnifiedPivots(): void
    {
        if (!Schema::hasTable('record_author')) {
            Schema::create('record_author', function (Blueprint $table) {
                $table->id();
                $table->foreignId('record_id')->constrained('records')->cascadeOnDelete();
                $table->foreignId('author_id')->constrained('authors')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['record_id', 'author_id']);
            });
        }

        if (!Schema::hasTable('record_keyword')) {
            Schema::create('record_keyword', function (Blueprint $table) {
                $table->id();
                $table->foreignId('record_id')->constrained('records')->cascadeOnDelete();
                $table->foreignId('keyword_id')->constrained('keywords')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['record_id', 'keyword_id']);
            });
        }

        if (!Schema::hasTable('record_thesaurus_concept')) {
            Schema::create('record_thesaurus_concept', function (Blueprint $table) {
                $table->id();
                $table->foreignId('record_id')->constrained('records')->cascadeOnDelete();
                $table->foreignId('concept_id')->constrained('thesaurus_concepts')->cascadeOnDelete();
                $table->float('weight')->nullable();
                $table->string('context')->nullable();
                $table->text('extraction_note')->nullable();
                $table->timestamps();
                $table->unique(['record_id', 'concept_id']);
            });
        }

        // dolly_records existe déjà (colonnes record_id, dolly_id) — seulement backfill.
        if (!Schema::hasTable('declassement_containers')) {
            Schema::create('declassement_containers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('declassement_list_id')->constrained('declassement_lists')->cascadeOnDelete();
                $table->foreignId('container_id')->constrained('containers')->cascadeOnDelete();
                $table->unsignedBigInteger('added_by')->nullable();
                $table->text('comment')->nullable();
                $table->timestamps();
                $table->unique(['declassement_list_id', 'container_id']);
                $table->foreign('added_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    private function backfillPivots(): void
    {
        $this->backfillTable(
            'record_keyword',
            'record_physical_keyword',
            'physical',
            'record_id',
            'keyword_id',
            ['keyword_id']
        );
        $this->backfillTable(
            'record_keyword',
            'record_digital_folder_keyword',
            'digital_folder',
            'folder_id',
            'keyword_id',
            ['keyword_id']
        );
        $this->backfillTable(
            'record_keyword',
            'record_digital_document_keyword',
            'digital_document',
            'document_id',
            'keyword_id',
            ['keyword_id']
        );

        $this->backfillTable(
            'record_thesaurus_concept',
            'record_physical_thesaurus_concept',
            'physical',
            'record_physical_id',
            'concept_id',
            ['concept_id', 'weight', 'context', 'extraction_note']
        );
        $this->backfillTable(
            'record_thesaurus_concept',
            'record_digital_folder_thesaurus_concept',
            'digital_folder',
            'folder_id',
            'concept_id',
            ['concept_id', 'weight', 'context', 'extraction_note']
        );
        $this->backfillTable(
            'record_thesaurus_concept',
            'record_digital_document_thesaurus_concept',
            'digital_document',
            'document_id',
            'concept_id',
            ['concept_id', 'weight', 'context', 'extraction_note']
        );

        $this->backfillTable(
            'record_author',
            'record_physical_author',
            'physical',
            'record_id',
            'author_id',
            ['author_id']
        );

        $this->backfillDolly();
        $this->backfillWorkplaces();
    }

    private function backfillTable(string $target, string $source, string $legacySource, string $sourceKey, string $targetKey, array $extra): void
    {
        if (!Schema::hasTable($source)) {
            return;
        }

        $rows = DB::table($source)->get();

        foreach ($rows as $row) {
            $recordId = DB::table('records')
                ->where('legacy_source', $legacySource)
                ->where('legacy_id', $row->{$sourceKey})
                ->value('id');

            if (!$recordId) {
                continue;
            }

            $payload = ['record_id' => $recordId, $targetKey => $row->{$targetKey}];
            foreach ($extra as $column) {
                if (isset($row->{$column})) {
                    $payload[$column] = $row->{$column};
                }
            }

            DB::table($target)->insertOrIgnore($payload);
        }
    }

    private function backfillDolly(): void
    {
        // Met à jour les dolly_records existants (record_id = id physique legacy → nouvel id).
        foreach (DB::table('dolly_records')->get() as $row) {
            $newId = DB::table('records')
                ->where('legacy_source', 'physical')
                ->where('legacy_id', $row->record_id)
                ->value('id');

            if ($newId) {
                DB::table('dolly_records')
                    ->where('record_id', $row->record_id)
                    ->where('dolly_id', $row->dolly_id)
                    ->update(['record_id' => $newId]);
            }
        }

        foreach ([
            ['dolly_slip_records', 'record_id'],
            ['dolly_digital_folders', 'folder_id'],
            ['dolly_digital_documents', 'document_id'],
        ] as [$table, $key]) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach (DB::table($table)->get() as $row) {
                $legacySource = match ($table) {
                    'dolly_slip_records' => 'physical',
                    'dolly_digital_folders' => 'digital_folder',
                    default => 'digital_document',
                };

                $recordId = DB::table('records')
                    ->where('legacy_source', $legacySource)
                    ->where('legacy_id', $row->{$key})
                    ->value('id');

                if ($recordId) {
                    DB::table('dolly_records')->insertOrIgnore([
                        'dolly_id' => $row->dolly_id,
                        'record_id' => $recordId,
                    ]);
                }
            }
        }
    }

    private function backfillWorkplaces(): void
    {
        if (Schema::hasTable('workplace_folders')) {
            foreach (DB::table('workplace_folders')->get(['id', 'folder_id']) as $row) {
                $recordId = DB::table('records')
                    ->where('legacy_source', 'digital_folder')
                    ->where('legacy_id', $row->folder_id)
                    ->value('id');

                if ($recordId) {
                    DB::table('workplace_folders')->where('id', $row->id)->update(['folder_id' => $recordId]);
                }
            }
        }

        if (Schema::hasTable('workplace_documents')) {
            foreach (DB::table('workplace_documents')->get(['id', 'document_id']) as $row) {
                $recordId = DB::table('records')
                    ->where('legacy_source', 'digital_document')
                    ->where('legacy_id', $row->document_id)
                    ->value('id');

                if ($recordId) {
                    DB::table('workplace_documents')->where('id', $row->id)->update(['document_id' => $recordId]);
                }
            }
        }
    }

    private function repointColumnForeignKeys(): void
    {
        foreach (['declassement_records', 'record_reactivations', 'record_physical_attachment'] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $fks = $this->foreignKeysFor($table);

                if (isset($fks['record_physical_id'])) {
                    $blueprint->dropForeign($fks['record_physical_id']);
                }
                if (Schema::hasColumn($table, 'record_physical_id')) {
                    $blueprint->renameColumn('record_physical_id', 'record_id');
                }
            });

            // Backfill : record_physical_id → records.id via legacy mapping
            $records = DB::table('records')->where('legacy_source', 'physical')->get(['legacy_id', 'id']);
            foreach ($records as $r) {
                DB::table($table)->where('record_id', $r->legacy_id)->update(['record_id' => $r->id]);
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->foreign('record_id')->references('id')->on('records')->cascadeOnDelete();
            });
        }
    }

    private function foreignKeysFor(string $table): array
    {
        $result = [];

        foreach (DB::select("SHOW CREATE TABLE {$table}") as $row) {
            $create = $row->{'Create Table'};
            preg_match_all('/CONSTRAINT `([^`]+)` FOREIGN KEY \(`([^`]+)`\)/s', $create, $matches, PREG_SET_ORDER);
            foreach ($matches as $m) {
                $result[$m[2]] = $m[1];
            }
        }

        return $result;
    }

    private function declassementAdditions(): void
    {
        Schema::table('declassement_lists', function (Blueprint $table) {
            if (!Schema::hasColumn('declassement_lists', 'digital_support')) {
                $table->boolean('digital_support')->default(false)->after('query_criteria');
            }
            if (!Schema::hasColumn('declassement_lists', 'analog_support')) {
                $table->boolean('analog_support')->default(false)->after('digital_support');
            }
            if (!Schema::hasColumn('declassement_lists', 'include_subrecords')) {
                $table->boolean('include_subrecords')->default(false)->after('analog_support');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('declassement_containers');

        Schema::table('declassement_lists', function (Blueprint $table) {
            $table->dropColumn(['digital_support', 'analog_support', 'include_subrecords']);
        });

        Schema::dropIfExists('dolly_records');
        Schema::dropIfExists('record_thesaurus_concept');
        Schema::dropIfExists('record_keyword');
        Schema::dropIfExists('record_author');
    }
};
