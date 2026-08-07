<?php

namespace Database\Seeders\AI;

use App\Models\AiSkill;
use App\Services\AI\AiSkillService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class AiSkillSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(AiSkillService::class);
        $service->ensureDirectories();

        $samples = [
            'redaction-archivistique' => [
                'name' => 'Rédaction archivistique',
                'description' => 'Rédige des notices, résumés et instruments de recherche conformes aux normes archivistiques (ISAD-G).',
                'version' => '1.0.0',
                'SKILL.md' => <<<'MD'
---
name: Rédaction archivistique
description: Rédige des notices, résumés et instruments de recherche conformes aux normes archivistiques (ISAD-G).
version: 1.0.0
---

# Rédaction archivistique

## Quand utiliser
- Générer une notice archivistique à partir d'un texte source
- Produire un résumé conforme aux zones ISAD-G
- Rédiger des instruments de recherche (guide, inventaire, répertoire)

## Règles
1. Toujours structurer en zones ISAD-G : identité, contexte, contenu et structure, conditions d'accès.
2. Le résumé doit rester factuel, sans interprétation.
3. Respecter la langue de la notice (français par défaut).
4. Terminer par 3 à 5 mots-clés archivistiques.
MD,
            ],
            'indexation' => [
                'name' => 'Indexation',
                'description' => 'Propose des mots-clés et concepts de thésaurus pour indexer les documents.',
                'version' => '1.0.0',
                'SKILL.md' => <<<'MD'
---
name: Indexation
description: Propose des mots-clés et concepts de thésaurus pour indexer les documents.
version: 1.0.0
---

# Indexation

## Quand utiliser
- Extraire des mots-clés pertinents d'un document
- Mettre en correspondance avec les concepts du thésaurus
- Suggérer une activité archivistique

## Règles
1. Proposer 5 à 8 mots-clés par document.
2. Privilégier les termes contrôlés du thésaurus (préfLabel puis altLabel).
3. Indiquer pour chaque terme sa catégorie entre crochets, ex. `[Sujet] contrat`.
4. Associer 2 à 3 synonymes par mot-clé séparés par des points-virgules.
MD,
            ],
        ];

        foreach ($samples as $slug => $data) {
            $folder = $service->systemPath() . DIRECTORY_SEPARATOR . $slug;
            if (!is_dir($folder)) {
                File::makeDirectory($folder, 0755, true);
                File::put($folder . '/SKILL.md', $data['SKILL.md']);
            }

            AiSkill::firstOrCreate(
                ['slug' => $slug, 'location' => 'system'],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'version' => $data['version'],
                    'folder' => $slug,
                    'enabled' => true,
                ]
            );
        }
    }
}
