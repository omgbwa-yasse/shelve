<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Communicability;
use App\Models\Organisation;
use App\Models\Record;
use App\Models\RecordLevel;
use App\Models\RecordMedium;
use App\Models\RecordStatus;
use App\Models\RecordSupport;
use App\Models\RecordType;
use App\Models\Retention;
use App\Models\Sort;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArchiveLifecycleDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $root = Organisation::where('code', 'DG-DEMO')->first() ?? Organisation::firstOrFail();
            $creator = User::where('email', 'admin@example.com')->first() ?? User::firstOrFail();
            $archiveOrganisation = Organisation::updateOrCreate(
                ['code' => 'ARCH-DEMO'],
                [
                    'name' => 'Service central des Archives - Démonstration',
                    'description' => 'Destination pédagogique des versements d’archives.',
                    'parent_id' => $root->id,
                ]
            );

            $communicability = Communicability::updateOrCreate(
                ['code' => 'COM-DEMO'],
                ['name' => 'Communicabilité après 3 ans', 'duration' => 3, 'description' => 'Règle pédagogique d’accès.']
            );

            $rules = [
                'DEMO-C10' => ['Conservation 10 ans', 10, 'C'],
                'DEMO-C00' => ['Conservation définitive immédiate', 0, 'C'],
                'DEMO-T10' => ['Tri après 10 ans', 10, 'T'],
                'DEMO-E05' => ['Élimination après 5 ans', 5, 'E'],
            ];

            foreach ($rules as $code => [$name, $duration, $sortCode]) {
                $rules[$code] = Retention::updateOrCreate(
                    ['code' => $code],
                    [
                        'name' => $name,
                        'duration' => $duration,
                        'sort_id' => Sort::where('code', $sortCode)->value('id'),
                    ]
                );
            }

            $classes = [
                'DEM-ACTIF' => ['Dossiers administratifs actifs', 'DEMO-C10'],
                'DEM-CONS' => ['Procès-verbaux à conservation définitive', 'DEMO-C00'],
                'DEM-TRI' => ['Contrats soumis au tri', 'DEMO-T10'],
                'DEM-ELIM' => ['Pièces comptables éliminables', 'DEMO-E05'],
            ];

            foreach ($classes as $code => [$name, $ruleCode]) {
                $activity = Activity::updateOrCreate(
                    ['code' => $code],
                    ['name' => $name, 'communicability_id' => $communicability->id]
                );
                $activity->retentions()->sync([$rules[$ruleCode]->id]);
                $root->activities()->syncWithoutDetaching([
                    $activity->id => ['creator_id' => $creator->id],
                ]);
            }

            $activityIds = Activity::whereIn('code', array_keys($classes))->pluck('id', 'code');
            $folderType = RecordType::where('code', 'PAPER_FOLDER')->firstOrFail();
            $level = RecordLevel::where('name', 'Dossier')->firstOrFail();
            $published = RecordStatus::where('name', 'Publié')->firstOrFail();
            $archived = RecordStatus::where('name', 'Archivé')->firstOrFail();
            $paper = RecordSupport::where('name', 'Papier')->firstOrFail();
            $records = [
                'DEMO-ARCH-ACTIF' => [
                    'Dossier actif — plan d’actions 2026', 'DEM-ACTIF', null, null, null, $root->id, $published->id,
                ],
                'DEMO-ARCH-TRF' => [
                    'Dossier fermé — transfert inter-direction à préparer', 'DEM-ACTIF', '2025-01-01', '2025-12-31', null, $root->id, $published->id,
                ],
                'DEMO-ARCH-INTER' => [
                    'Dossier en conservation intermédiaire', 'DEM-ACTIF', '2023-01-01', '2023-12-31', '2024-02-15', $root->id, $archived->id,
                ],
                'DEMO-ARCH-CONS' => [
                    'Procès-verbaux à verser en conservation définitive', 'DEM-CONS', '2018-01-01', '2018-12-31', '2019-02-10', $root->id, $archived->id,
                ],
                'DEMO-ARCH-TRI' => [
                    'Contrats arrivés à échéance — tri requis', 'DEM-TRI', '2009-01-01', '2010-12-31', '2011-03-01', $root->id, $archived->id,
                ],
                'DEMO-ARCH-ELIM' => [
                    'Pièces comptables 2018 — élimination à soumettre', 'DEM-ELIM', '2018-01-01', '2018-12-31', '2019-03-01', $root->id, $archived->id,
                ],
                'DEMO-ARCH-DEPOSE' => [
                    'Registre déjà versé et conservé définitivement', 'DEM-CONS', '2015-01-01', '2015-12-31', '2016-02-01', $archiveOrganisation->id, $archived->id,
                ],
            ];

            foreach ($records as $code => [$name, $activityCode, $startDate, $closingDate, $transferDate, $organisationId, $statusId]) {
                $record = Record::updateOrCreate(
                    ['code' => $code],
                    [
                        'name' => $name,
                        'description' => 'Notice pédagogique CSPH/SHELVE pour la démonstration du cycle de vie.',
                        'type_id' => $folderType->id,
                        'level_id' => $level->id,
                        'status_id' => $statusId,
                        'activity_id' => $activityIds[$activityCode],
                        'organisation_id' => $organisationId,
                        'creator_id' => $creator->id,
                        'opening_date' => $startDate ?: '2026-01-01',
                        'start_date' => $startDate ?: '2026-01-01',
                        'end_date' => $closingDate,
                        'closing_date' => $closingDate,
                        'transfer_effective_date' => $transferDate,
                        'deposit_approved_date' => $code === 'DEMO-ARCH-DEPOSE' ? '2016-02-01' : null,
                        'deposit_effective_date' => $code === 'DEMO-ARCH-DEPOSE' ? '2016-02-15' : null,
                        'archival_status_gvaa' => $code === 'DEMO-ARCH-DEPOSE' ? 'deposited' : null,
                        'date_format' => 'D',
                        'version_number' => 1,
                        'is_current_version' => true,
                        'metadata' => ['demo' => true, 'scope' => 'archive_lifecycle'],
                    ]
                );

                RecordMedium::updateOrCreate(
                    ['record_id' => $record->id, 'support_id' => $paper->id],
                    ['status' => RecordMedium::STATUS_FINAL, 'is_principal' => true]
                );
            }
        });
    }
}
