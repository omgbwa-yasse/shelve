<?php

namespace Database\Seeders\Tools;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Retention;
use App\Models\Sort;
use App\Models\Law;
use App\Models\LawArticle;
use App\Models\Activity;

class RetentionLawSeeder extends Seeder
{
    /**
     * Seed test data for the Tools (Outils) module — Retention rules & Laws.
     * Creates sorts, retention rules, laws, law articles, and pivot links.
     * Idempotent: uses firstOrCreate/updateOrInsert.
     */
    public function run(): void
    {
        $this->command->info('⚙️ Seeding Tools (Retention & Laws) module test data...');

        // --- 1. Sorts (final disposition) ---
        $sortDefs = [
            ['code' => 'E', 'name' => 'Élimination', 'description' => 'Les documents sont détruits à l\'issue du délai de conservation.'],
            ['code' => 'T', 'name' => 'Tri',         'description' => 'Les documents font l\'objet d\'un tri sélectif avant versement aux archives définitives.'],
            ['code' => 'C', 'name' => 'Conservation', 'description' => 'Les documents sont conservés définitivement aux archives historiques.'],
        ];

        $sorts = [];
        foreach ($sortDefs as $sd) {
            $sorts[$sd['code']] = Sort::firstOrCreate(
                ['code' => $sd['code']],
                ['name' => $sd['name'], 'description' => $sd['description']]
            );
        }

        // --- 2. Laws ---
        // Ensure law types exist first
        $lawType1 = DB::table('law_types')->first();
        if (!$lawType1) {
            DB::table('law_types')->insert([
                ['name' => 'Loi', 'description' => 'Texte législatif', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Décret', 'description' => 'Texte réglementaire', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Arrêté', 'description' => 'Décision administrative', 'created_at' => now(), 'updated_at' => now()],
            ]);
            $lawType1 = DB::table('law_types')->first();
        }
        $lawDefs = [
            [
                'code' => 'LOI-001',
                'name' => 'Loi n° 88-09 relative aux archives nationales',
                'publish_date' => '1988-01-26',
                'description' => 'Loi régissant la gestion, la conservation et la communication des archives publiques.',
            ],
            [
                'code' => 'LOI-002',
                'name' => 'Décret n° 98-236 modalités d\'application',
                'publish_date' => '1998-10-18',
                'description' => 'Décret fixant les modalités d\'application de la loi 88-09 relative aux archives nationales.',
            ],
            [
                'code' => 'LOI-003',
                'name' => 'Loi n° 2008-09 relative au droit d\'accès à l\'information',
                'publish_date' => '2008-02-25',
                'description' => 'Loi garantissant le droit d\'accès aux documents administratifs.',
            ],
        ];

        $createdLaws = [];
        foreach ($lawDefs as $ld) {
            $data = [
                'name' => $ld['name'],
                'publish_date' => $ld['publish_date'],
                'description' => $ld['description'],
                'law_type_id' => $lawType1->id,
            ];
            $createdLaws[] = Law::firstOrCreate(['code' => $ld['code']], $data);
        }

        // --- 3. Law Articles ---
        $articleDefs = [
            ['law_index' => 0, 'code' => 'ART-001', 'name' => 'Article 1 — Définition des archives', 'description' => 'Sont considérés comme archives l\'ensemble des documents produits ou reçus par toute personne physique ou morale.'],
            ['law_index' => 0, 'code' => 'ART-002', 'name' => 'Article 5 — Délais de conservation', 'description' => 'Les délais de conservation sont fixés par les tableaux de gestion élaborés par chaque institution.'],
            ['law_index' => 0, 'code' => 'ART-003', 'name' => 'Article 12 — Communication des archives', 'description' => 'Les archives publiques sont communicables de plein droit après un délai de 25 ans.'],
            ['law_index' => 1, 'code' => 'ART-004', 'name' => 'Article 3 — Versement obligatoire', 'description' => 'Les administrations sont tenues de verser leurs archives conformément aux calendriers de conservation.'],
            ['law_index' => 1, 'code' => 'ART-005', 'name' => 'Article 8 — Conditions de conservation', 'description' => 'Les locaux de conservation doivent répondre aux normes de température et d\'hygrométrie.'],
            ['law_index' => 2, 'code' => 'ART-006', 'name' => 'Article 1 — Droit d\'accès', 'description' => 'Tout citoyen a le droit d\'accéder aux documents administratifs dans les conditions prévues par la loi.'],
        ];

        $createdArticles = [];
        foreach ($articleDefs as $ad) {
            $createdArticles[] = LawArticle::firstOrCreate(
                ['code' => $ad['code']],
                [
                    'name' => $ad['name'],
                    'description' => $ad['description'],
                    'law_id' => $createdLaws[$ad['law_index']]->id,
                ]
            );
        }

        // --- 4. Retention Rules ---
        $retentionDefs = [
            ['code' => 'RET-001', 'name' => 'Dossiers du personnel',              'duration' => 80,  'sort_code' => 'T'],
            ['code' => 'RET-002', 'name' => 'Pièces comptables',                   'duration' => 10,  'sort_code' => 'E'],
            ['code' => 'RET-003', 'name' => 'Procès-verbaux du conseil',           'duration' => 0,   'sort_code' => 'C'], // 0 = permanent
            ['code' => 'RET-004', 'name' => 'Correspondance courante',             'duration' => 5,   'sort_code' => 'E'],
            ['code' => 'RET-005', 'name' => 'Rapports annuels',                    'duration' => 0,   'sort_code' => 'C'],
            ['code' => 'RET-006', 'name' => 'Contrats fournisseurs',               'duration' => 10,  'sort_code' => 'T'],
            ['code' => 'RET-007', 'name' => 'Documents de politique générale',     'duration' => 0,   'sort_code' => 'C'],
            ['code' => 'RET-008', 'name' => 'Dossiers de marchés publics',         'duration' => 15,  'sort_code' => 'T'],
        ];

        $createdRetentions = [];
        foreach ($retentionDefs as $rd) {
            $createdRetentions[] = Retention::firstOrCreate(
                ['code' => $rd['code']],
                [
                    'name' => $rd['name'],
                    'duration' => $rd['duration'],
                    'sort_id' => $sorts[$rd['sort_code']]->id,
                ]
            );
        }

        // --- 5. Retention ↔ Activity pivot ---
        $activities = Activity::take(5)->get();
        if ($activities->isNotEmpty()) {
            foreach ($createdRetentions as $i => $ret) {
                $act = $activities[$i % $activities->count()];
                DB::table('retention_activity')->updateOrInsert(
                    ['retention_id' => $ret->id, 'activity_id' => $act->id]
                );
            }
        }

        // --- 6. Retention ↔ LawArticle pivot ---
        // Link retentions to relevant law articles
        $links = [
            [0, 1], // Dossiers du personnel ↔ Art 5 (délais)
            [1, 1], // Pièces comptables ↔ Art 5
            [2, 2], // PV conseil ↔ Art 12 (communication)
            [3, 0], // Correspondance ↔ Art 1 (définition)
            [4, 2], // Rapports ↔ Art 12
            [5, 3], // Contrats ↔ Art 3 (versement)
            [6, 5], // Politique ↔ Art 1 droit d'accès
            [7, 3], // Marchés ↔ Art 3 (versement)
        ];

        foreach ($links as [$retIdx, $artIdx]) {
            if (isset($createdRetentions[$retIdx], $createdArticles[$artIdx])) {
                DB::table('retention_law_articles')->updateOrInsert(
                    ['retention_id' => $createdRetentions[$retIdx]->id, 'law_article_id' => $createdArticles[$artIdx]->id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        $this->command->info('✅ Tools: ' . count($sorts) . ' sorts, ' . count($createdLaws) . ' laws, ' . count($createdArticles) . ' articles, ' . count($createdRetentions) . ' retentions seeded.');
    }
}
