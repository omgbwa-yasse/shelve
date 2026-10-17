<?php

namespace Database\Seeders\Transfers;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Slip;
use App\Models\SlipRecord;
use App\Models\SlipStatus;
use App\Models\RecordLevel;
use App\Models\RecordSupport;
use App\Models\Activity;
use App\Models\User;
use App\Models\Organisation;
use App\Models\Keyword;
use App\Models\Container;

class TransferDataSeeder extends Seeder
{
    /**
     * Seed test data for the Transfers (Transferts / Bordereaux) module.
     * Creates slips in all statuses, with slip-records, container links, and keywords.
     * Idempotent: uses firstOrCreate/updateOrInsert.
     */
    public function run(): void
    {
        $this->command->info('📦 Seeding Transfers module test data...');

        $users = User::take(4)->get();
        $orgs = Organisation::take(2)->get();
        $statuses = SlipStatus::all();
        $levels = RecordLevel::all();
        $supports = RecordSupport::all();
        $activity = Activity::first();
        $keywords = Keyword::take(4)->get();
        $containers = Container::take(3)->get();

        if ($users->count() < 2 || $orgs->isEmpty()) {
            $this->command->warn('⚠️  Need users and organisations. Run UserSeeder first.');
            return;
        }
        if ($statuses->isEmpty()) {
            $this->command->warn('⚠️  No slip statuses found. Run SlipStatusSeeder first.');
            return;
        }

        $officer = $users[0];
        $user2 = $users[1] ?? $users[0];
        $user3 = $users[2] ?? $users[0];
        $org1 = $orgs[0];
        $org2 = $orgs[1] ?? $orgs[0];

        $levelDossier = $levels->firstWhere('name', 'Dossier') ?? $levels->first();
        $levelPiece = $levels->firstWhere('name', 'Pièce') ?? $levels->last();
        $supportPaper = $supports->firstWhere('name', 'Papier') ?? $supports->first();

        // --- 1. Slips (Bordereaux de versement) ---
        $slipDefs = [
            [
                'code' => 'BDV-2026-001', 'name' => 'Bordereau DRH – Dossiers du personnel 2020-2024',
                'description' => 'Versement des dossiers individuels du personnel couvrant la période 2020-2024.',
                'status_index' => 0,
                'is_received' => false, 'is_approved' => false, 'is_integrated' => false,
            ],
            [
                'code' => 'BDV-2026-002', 'name' => 'Bordereau Comptabilité – Exercice 2023',
                'description' => 'Versement des pièces comptables de l\'exercice 2023.',
                'status_index' => min(1, $statuses->count() - 1),
                'is_received' => true, 'received_date' => now()->subDays(10),
                'is_approved' => false, 'is_integrated' => false,
            ],
            [
                'code' => 'BDV-2026-003', 'name' => 'Bordereau Direction – Rapports annuels',
                'description' => 'Versement des rapports annuels et procès-verbaux du conseil.',
                'status_index' => min(2, $statuses->count() - 1),
                'is_received' => true, 'received_date' => now()->subDays(30),
                'is_approved' => true, 'approved_date' => now()->subDays(20),
                'is_integrated' => false,
            ],
            [
                'code' => 'BDV-2026-004', 'name' => 'Bordereau IT – Documentation technique',
                'description' => 'Versement de la documentation technique et des manuels d\'utilisation.',
                'status_index' => min(3, $statuses->count() - 1),
                'is_received' => true, 'received_date' => now()->subDays(60),
                'is_approved' => true, 'approved_date' => now()->subDays(45),
                'is_integrated' => true, 'integrated_date' => now()->subDays(30),
            ],
            [
                'code' => 'BDV-2026-005', 'name' => 'Bordereau Juridique – Contrats fournisseurs',
                'description' => 'Versement des contrats avec les fournisseurs et prestataires.',
                'status_index' => 0,
                'is_received' => false, 'is_approved' => false, 'is_integrated' => false,
            ],
        ];

        $createdSlips = [];
        foreach ($slipDefs as $def) {
            $slip = Slip::firstOrCreate(
                ['code' => $def['code']],
                [
                    'name' => $def['name'],
                    'description' => $def['description'],
                    'officer_organisation_id' => $org1->id,
                    'officer_id' => $officer->id,
                    'user_organisation_id' => $org2->id,
                    'user_id' => $user2->id,
                    'slip_status_id' => $statuses[$def['status_index']]->id,
                    'is_received' => $def['is_received'],
                    'received_date' => $def['received_date'] ?? null,
                    'received_by' => ($def['is_received'] ?? false) ? $user3->id : null,
                    'is_approved' => $def['is_approved'],
                    'approved_date' => $def['approved_date'] ?? null,
                    'approved_by' => ($def['is_approved'] ?? false) ? $officer->id : null,
                    'is_integrated' => $def['is_integrated'],
                    'integrated_date' => $def['integrated_date'] ?? null,
                    'integrated_by' => ($def['is_integrated'] ?? false) ? $officer->id : null,
                ]
            );
            $createdSlips[] = $slip;
        }

        // --- 2. SlipRecords ---
        $slipRecordDefs = [
            // Slip 1: DRH dossiers
            ['slip_index' => 0, 'code' => 'SR-001', 'name' => 'Dossier DUPONT Jean', 'level' => $levelDossier, 'date_start' => '2000', 'date_end' => '2020', 'width' => 3.5],
            ['slip_index' => 0, 'code' => 'SR-002', 'name' => 'Dossier MARTIN Marie', 'level' => $levelDossier, 'date_start' => '2005', 'date_end' => '2024', 'width' => 2.0],
            ['slip_index' => 0, 'code' => 'SR-003', 'name' => 'Registre des entrées 2020-2024', 'level' => $levelPiece, 'date_start' => '2020', 'date_end' => '2024', 'width' => 0.5],
            // Slip 2: Comptabilité
            ['slip_index' => 1, 'code' => 'SR-004', 'name' => 'Journal comptable 2023', 'level' => $levelDossier, 'date_start' => '2023', 'date_end' => '2023', 'width' => 5.0],
            ['slip_index' => 1, 'code' => 'SR-005', 'name' => 'Factures fournisseurs Q1 2023', 'level' => $levelPiece, 'date_start' => '2023-01', 'date_end' => '2023-03', 'width' => 1.5],
            // Slip 3: Rapports
            ['slip_index' => 2, 'code' => 'SR-006', 'name' => 'Rapports annuels 2018-2023', 'level' => $levelDossier, 'date_start' => '2018', 'date_end' => '2023', 'width' => 4.0],
            ['slip_index' => 2, 'code' => 'SR-007', 'name' => 'Procès-verbaux du conseil 2020-2023', 'level' => $levelDossier, 'date_start' => '2020', 'date_end' => '2023', 'width' => 3.0],
            // Slip 4: Documentation IT
            ['slip_index' => 3, 'code' => 'SR-008', 'name' => 'Manuel utilisateur — Shelve v2', 'level' => $levelPiece, 'date_start' => '2024', 'date_end' => '2024', 'width' => 0.3],
            // Slip 5: Juridique
            ['slip_index' => 4, 'code' => 'SR-009', 'name' => 'Contrats prestataires 2021-2025', 'level' => $levelDossier, 'date_start' => '2021', 'date_end' => '2025', 'width' => 6.0],
            ['slip_index' => 4, 'code' => 'SR-010', 'name' => 'Avenants et modifications', 'level' => $levelPiece, 'date_start' => '2022', 'date_end' => '2025', 'width' => 1.0],
        ];

        $createdRecords = [];
        foreach ($slipRecordDefs as $srDef) {
            $sr = SlipRecord::firstOrCreate(
                ['slip_id' => $createdSlips[$srDef['slip_index']]->id, 'code' => $srDef['code']],
                [
                    'name' => $srDef['name'],
                    'date_format' => 'Y',
                    'date_start' => $srDef['date_start'],
                    'date_end' => $srDef['date_end'],
                    'content' => null,
                    'level_id' => $srDef['level']->id,
                    'width' => $srDef['width'],
                    'support_id' => $supportPaper?->id,
                    'activity_id' => $activity?->id,
                    'creator_id' => $officer->id,
                ]
            );
            $createdRecords[] = $sr;
        }

        // --- 3. SlipRecord ↔ Container associations ---
        if ($containers->isNotEmpty()) {
            foreach ($createdRecords as $i => $sr) {
                if ($i >= $containers->count()) break;
                DB::table('slip_record_container')->updateOrInsert(
                    ['slip_record_id' => $sr->id, 'container_id' => $containers[$i % $containers->count()]->id],
                    [
                        'creator_id' => $officer->id,
                        'description' => "Conditionnement du versement {$sr->code}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // --- 4. SlipRecord ↔ Keyword associations ---
        if ($keywords->isNotEmpty()) {
            foreach ($createdRecords as $i => $sr) {
                $kw = $keywords[$i % $keywords->count()];
                DB::table('slip_record_keyword')->updateOrInsert(
                    ['slip_record_id' => $sr->id, 'keyword_id' => $kw->id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        $this->command->info('✅ Transfers: ' . count($createdSlips) . ' slips, ' . count($createdRecords) . ' slip-records seeded.');
    }
}
