<?php

namespace Database\Seeders\Records;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Record;
use App\Models\RecordPhysical;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\RecordSupport;
use App\Models\Activity;
use App\Models\User;
use App\Models\Organisation;
use App\Models\Keyword;
use App\Models\Author;

class RecordDataSeeder extends Seeder
{
    /**
     * Seed test data for the Records (Répertoire) module.
     * Creates a full ISAD(G) hierarchy (Fonds→Sous-fonds→Série→Dossier→Pièce),
     * accessions, and record-container/author/keyword links.
     * Idempotent: uses firstOrCreate.
     */
    public function run(): void
    {
        $this->command->info('📁 Seeding Records module test data...');

        $user = User::first();
        $org = Organisation::first();
        if (!$user || !$org) {
            $this->command->warn('⚠️  No users/organisations found. Run SuperAdminSeeder + OrganisationSeeder first.');
            return;
        }

        $levels = RecordLevel::all()->keyBy('name');
        $statuses = RecordStatus::all();
        $supports = RecordSupport::all();
        $activity = Activity::first();
        $keywords = Keyword::take(5)->get();
        $authors = Author::take(3)->get();

        if ($levels->isEmpty() || $statuses->isEmpty()) {
            $this->command->warn('⚠️  Missing record levels/statuses. Run RecordLevelSeeder + RecordStatusSeeder first.');
            return;
        }

        $statusPublished = $statuses->firstWhere('name', 'published') ?? $statuses->first();
        $statusDraft = $statuses->firstWhere('name', 'draft') ?? $statuses->first();
        $supportPaper = $supports->firstWhere('name', 'Papier') ?? $supports->first();

        // Helper to find level by name pattern
        $findLevel = function (string $pattern) use ($levels) {
            foreach ($levels as $name => $level) {
                if (stripos($name, $pattern) !== false) return $level;
            }
            return $levels->first();
        };

        // --- 1. Archives Hierarchy (ISAD(G)) ---
        // Fonds
        $fonds = RecordPhysical::firstOrCreate(
            ['code' => 'F-001'],
            [
                'name' => 'Fonds de la Direction Générale',
                'date_format' => 'Y', 'date_start' => '1960', 'date_end' => '2025',
                'level_id' => $findLevel('Fonds')->id,
                'status_id' => $statusPublished->id,
                'support_id' => $supportPaper?->id,
                'activity_id' => $activity?->id,
                'user_id' => $user->id,
                'width' => '120',
                'width_description' => '120 mètres linéaires',
                'biographical_history' => 'Fonds constitué depuis la création de l\'institution en 1960.',
                'archival_history' => 'Archives conservées dans le bâtiment principal depuis 1975.',
                'content' => 'Documents administratifs, correspondance officielle, rapports annuels, dossiers du personnel.',
                'access_conditions' => 'Libre après 25 ans',
                'arrangement' => 'Chronologique et thématique',
                'finding_aids' => 'Répertoire numérique détaillé',
            ]
        );

        // Sous-fonds
        $sousFonds1 = RecordPhysical::firstOrCreate(
            ['code' => 'SF-001'],
            [
                'name' => 'Sous-fonds Ressources Humaines',
                'date_format' => 'Y', 'date_start' => '1975', 'date_end' => '2024',
                'level_id' => $findLevel('Sous-fonds')->id ?? $findLevel('Fonds')->id,
                'status_id' => $statusPublished->id,
                'support_id' => $supportPaper?->id,
                'parent_id' => $fonds->id,
                'activity_id' => $activity?->id,
                'user_id' => $user->id,
                'content' => 'Dossiers du personnel, fiches de paie, contrats de travail.',
            ]
        );

        $sousFonds2 = RecordPhysical::firstOrCreate(
            ['code' => 'SF-002'],
            [
                'name' => 'Sous-fonds Comptabilité et Finances',
                'date_format' => 'Y', 'date_start' => '1980', 'date_end' => '2023',
                'level_id' => $findLevel('Sous-fonds')->id ?? $findLevel('Fonds')->id,
                'status_id' => $statusPublished->id,
                'support_id' => $supportPaper?->id,
                'parent_id' => $fonds->id,
                'activity_id' => $activity?->id,
                'user_id' => $user->id,
                'content' => 'Budgets, états financiers, pièces comptables.',
            ]
        );

        // Séries
        $serie1 = RecordPhysical::firstOrCreate(
            ['code' => 'S-001'],
            [
                'name' => 'Série — Dossiers individuels du personnel',
                'date_format' => 'Y', 'date_start' => '1985', 'date_end' => '2024',
                'level_id' => $findLevel('Série')->id ?? $findLevel('Fonds')->id,
                'status_id' => $statusPublished->id,
                'support_id' => $supportPaper?->id,
                'parent_id' => $sousFonds1->id,
                'activity_id' => $activity?->id,
                'user_id' => $user->id,
                'content' => 'Dossiers individuels classés par ordre alphabétique.',
            ]
        );

        $serie2 = RecordPhysical::firstOrCreate(
            ['code' => 'S-002'],
            [
                'name' => 'Série — Budgets annuels',
                'date_format' => 'Y', 'date_start' => '1990', 'date_end' => '2023',
                'level_id' => $findLevel('Série')->id ?? $findLevel('Fonds')->id,
                'status_id' => $statusPublished->id,
                'support_id' => $supportPaper?->id,
                'parent_id' => $sousFonds2->id,
                'activity_id' => $activity?->id,
                'user_id' => $user->id,
                'content' => 'Budgets prévisionnels et état d\'exécution budgétaire.',
            ]
        );

        // Dossiers
        $dossiers = [
            ['code' => 'D-001', 'name' => 'Dossier DUPONT Jean', 'parent_id' => $serie1->id, 'date_start' => '2000', 'date_end' => '2020', 'content' => 'Contrat, avenants, évaluations, congés.'],
            ['code' => 'D-002', 'name' => 'Dossier MARTIN Marie', 'parent_id' => $serie1->id, 'date_start' => '2005', 'date_end' => '2024', 'content' => 'Contrat, formations, avancements.'],
            ['code' => 'D-003', 'name' => 'Budget prévisionnel 2022', 'parent_id' => $serie2->id, 'date_start' => '2022', 'date_end' => '2022', 'content' => 'Budget prévisionnel approuvé pour l\'exercice 2022.'],
            ['code' => 'D-004', 'name' => 'Budget prévisionnel 2023', 'parent_id' => $serie2->id, 'date_start' => '2023', 'date_end' => '2023', 'content' => 'Budget prévisionnel approuvé pour l\'exercice 2023.'],
        ];

        $createdDossiers = [];
        foreach ($dossiers as $d) {
            $dossier = RecordPhysical::firstOrCreate(
                ['code' => $d['code']],
                array_merge($d, [
                    'date_format' => 'Y',
                    'level_id' => $findLevel('Dossier')->id ?? $findLevel('Fonds')->id,
                    'status_id' => $statusPublished->id,
                    'support_id' => $supportPaper?->id,
                    'activity_id' => $activity?->id,
                    'user_id' => $user->id,
                ])
            );
            $createdDossiers[] = $dossier;
        }

        // Pièces (documents)
        $pieces = [
            ['code' => 'P-001', 'name' => 'Contrat de travail — DUPONT Jean', 'parent_id' => $createdDossiers[0]->id, 'date_exact' => '2000-03-15', 'content' => 'Contrat à durée indéterminée.'],
            ['code' => 'P-002', 'name' => 'Évaluation annuelle 2019 — DUPONT Jean', 'parent_id' => $createdDossiers[0]->id, 'date_exact' => '2019-12-20', 'content' => 'Évaluation de performance annuelle.'],
            ['code' => 'P-003', 'name' => 'Contrat de travail — MARTIN Marie', 'parent_id' => $createdDossiers[1]->id, 'date_exact' => '2005-09-01', 'content' => 'Contrat à durée déterminée, renouvelé en CDI.'],
            ['code' => 'P-004', 'name' => 'État d\'exécution budgétaire Q4 2022', 'parent_id' => $createdDossiers[2]->id, 'date_exact' => '2022-12-31', 'content' => 'Rapport d\'exécution du dernier trimestre.'],
        ];

        $createdPieces = [];
        foreach ($pieces as $p) {
            $piece = RecordPhysical::firstOrCreate(
                ['code' => $p['code']],
                array_merge($p, [
                    'date_format' => 'D',
                    'level_id' => $findLevel('Pièce')->id ?? $findLevel('Fonds')->id,
                    'status_id' => $statusPublished->id,
                    'support_id' => $supportPaper?->id,
                    'activity_id' => $activity?->id,
                    'user_id' => $user->id,
                ])
            );
            $createdPieces[] = $piece;
        }

        // --- 3. Record ↔ Keyword associations ---
        if ($keywords->isNotEmpty()) {
            foreach ($createdDossiers as $i => $dossier) {
                $kw = $keywords[$i % $keywords->count()];
                DB::table('record_physical_keyword')->updateOrInsert(
                    ['record_physical_id' => $dossier->id, 'keyword_id' => $kw->id],
                    ['created_at' => now()]
                );
            }
        }

        // --- 4. Record ↔ Author associations ---
        if ($authors->isNotEmpty()) {
            foreach ([$fonds, $sousFonds1, $sousFonds2] as $i => $record) {
                $author = $authors[$i % $authors->count()];
                DB::table('record_physical_author')->updateOrInsert(
                    ['record_physical_id' => $record->id, 'author_id' => $author->id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        // --- 5. Record ↔ Container associations ---
        $containers = DB::table('containers')->take(4)->get();
        if ($containers->isNotEmpty()) {
            foreach ($createdDossiers as $i => $dossier) {
                $container = $containers[$i % $containers->count()];
                DB::table('record_physical_container')->updateOrInsert(
                    ['record_physical_id' => $dossier->id, 'container_id' => $container->id],
                    ['description' => 'Auto-seeded', 'creator_id' => $user->id, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        $totalRecords = 1 + 2 + 2 + count($createdDossiers) + count($createdPieces); // fonds + sous-fonds + series + dossiers + pieces
        $this->command->info("✅ Records module: $totalRecords records (ISAD(G) hierarchy) seeded.");
    }
}
