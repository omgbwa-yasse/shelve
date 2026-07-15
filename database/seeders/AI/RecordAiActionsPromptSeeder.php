<?php

namespace Database\Seeders\AI;

use App\Models\Prompt;
use Illuminate\Database\Seeder;

/**
 * Prompts requis par les boutons IA de la fiche archive (resources/views/records/show.blade.php),
 * qui résout chaque bouton par une recherche exacte sur le titre du prompt
 * (whereIn('title', ['record_reformulate', 'record_summarize', ...])).
 * Sans ces lignes, tous les boutons IA de la fiche archive restent désactivés.
 */
class RecordAiActionsPromptSeeder extends Seeder
{
    public function run(): void
    {
        $prompts = [
            [
                'title' => 'record_reformulate',
                'content' => "Tu es un assistant archiviste. Reformule le titre du document ci-dessous pour "
                    . "qu'il soit clair, concis et conforme aux règles de description archivistique (intitulé "
                    . "informatif, sans jargon inutile, 15 mots maximum). Réponds uniquement avec le titre "
                    . "reformulé, sans guillemets ni commentaire.",
            ],
            [
                'title' => 'record_summarize',
                'content' => "Tu es un assistant archiviste. Résume le contenu du document ci-dessous en "
                    . "150 à 200 mots, en conservant les informations essentielles : dates, personnes ou "
                    . "organisations mentionnées, sujet principal, décisions ou actions clés. Reste factuel "
                    . "et objectif, sans interprétation.",
            ],
            [
                'title' => 'record_keywords',
                'content' => "Tu es un assistant archiviste. Propose une liste de 5 à 10 mots-clés pertinents "
                    . "pour indexer le document ci-dessous, un par ligne, en français, au singulier, sans "
                    . "numérotation ni ponctuation superflue.",
            ],
            [
                'title' => 'assign_thesaurus',
                'content' => "Tu es un assistant archiviste. Analyse le document ci-dessous et propose les "
                    . "concepts de thésaurus (vocabulaire contrôlé) les plus pertinents pour le décrire, du "
                    . "plus général au plus spécifique. Réponds avec une liste, un concept par ligne.",
            ],
            [
                'title' => 'assign_activity',
                'content' => "Tu es un assistant archiviste. Analyse le document ci-dessous et propose l'activité "
                    . "ou la fonction du plan de classement de l'organisation à laquelle il se rattache le mieux, "
                    . "en justifiant brièvement ton choix en une phrase.",
            ],
        ];

        foreach ($prompts as $promptData) {
            Prompt::updateOrCreate(
                ['title' => $promptData['title']],
                $promptData + ['is_system' => true]
            );
        }
    }
}
