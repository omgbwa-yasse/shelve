<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class AiSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $this->seedPrompts($now);
        // Seed legacy ai_global_settings only if the table exists to avoid duplication
        if (Schema::hasTable('ai_global_settings')) {
            $this->seedSettings($now);
        }
    }

    private function seedPrompts($now): void
    {
        $ex = "Exemples :\n"; $ex1 = "Exemple :\n"; $fmt = "Format :\n";
        $prompts = array_merge(
            $this->promptsReformulate($ex, $ex1),
            $this->promptsSummarize($fmt),
            $this->promptsAssignThesaurus($fmt),
            $this->promptsKeywords(),
            [
                [
                    'title' => 'assign_activity',
                    'content' => "Tu aides à l'indexation par activités. Choisis les activités les plus pertinentes parmi une liste fournie, en te basant uniquement sur le contenu.",
                    'is_system' => true,
                ],
                [
                    'title' => 'action.assign_activity.user',
                    'content' =>
                        "Voici la liste complète des activités disponibles (id | code | name), une par ligne :\n" .
                        "{{activities}}\n\n" .
                        "Contexte (si présent) :\n{{context}}\n\n" .
                        "Tâche : choisis l'activité la plus pertinente (selected) et propose une alternative (alternative).\n" .
                        "Réponds STRICTEMENT en JSON valide (sans texte additionnel) avec ce schéma :\n" .
                        "{\n  \"selected\": { \"id\": <int|null>, \"code\": <string|null>, \"name\": <string|null> },\n  \"alternative\": { \"id\": <int|null>, \"code\": <string|null>, \"name\": <string|null> },\n  \"confidence\": <number 0..1>,\n  \"reason\": <string court en FR>\n}\n",
                    'is_system' => false,
                ],
                [
                    'title' => 'slip_summarize',
                    'content' => "Tu es un assistant archivistique. Génère un résumé synthétique d'un ensemble de slips (bordereaux), en mettant en évidence les points saillants.",
                    'is_system' => true,
                ],
            ]
        );

        foreach ($prompts as $p) {
            $keys = ['title' => $p['title']];
            if (Schema::hasColumn('prompts', 'is_system')) {
                $keys['is_system'] = (bool) $p['is_system'];
            }
            if (Schema::hasColumn('prompts', 'organisation_id')) {
                $keys['organisation_id'] = null;
            }
            if (Schema::hasColumn('prompts', 'user_id')) {
                $keys['user_id'] = null;
            }

            $updates = [
                'content' => $p['content'],
                'updated_at' => $now,
                'created_at' => $now,
            ];
            if (Schema::hasColumn('prompts', 'is_system')) {
                $updates['is_system'] = (bool) $p['is_system'];
            }

            DB::table('prompts')->updateOrInsert($keys, $updates);
        }
    }

    private function promptsReformulate(string $ex, string $ex1): array
    {
        return [
            [
                'title' => 'record_reformulate',
                'content' =>
                    "Tu es un assistant archivistique. Reformule les intitulés de dossiers selon les règles archivistiques françaises afin qu'ils soient clairs, concis et informatifs.\n\n" .
                    "Règles de présentation :\n" .
                    "• Point-tiret (. —) pour séparer l'objet principal du reste (recommandé).\n" .
                    "• Virgule (,) : données de même niveau ; point-virgule (;) : éléments d'analyse de même nature ; deux points (:) : typologie ; point (.) : termine une sous-partie.\n" .
                    "• Mise en facteur commun : regrouper le commun dans des niveaux intermédiaires, du général vers le particulier ; éviter 'Idem'.\n" .
                    "• Mots-outils utiles : avec, dont, contient, concerne, en particulier, notamment, aussi, ne concerne que.\n\n" .
                    "1) Intitulé à UN objet\n" .
                    "Structure de base : Objet, action : typologie documentaire. Dates extrêmes\n" .
                    $ex .
                    "- Rouen, aménagement du quartier Sainte-Thérèse. 1956-1985\n" .
                    "- Commission de surveillance de la prison départementale de Lons-le-Saunier : registres des délibérations. 1827\n" .
                    "- Personnel de la mairie, attribution de la médaille du travail : liste des bénéficiaires. 1950-1960\n" .
                    "Variante point-tiret (recommandée) : Objet. — Action : typologie documentaire. Dates extrêmes\n" .
                    $ex .
                    "- Rouen. — Aménagement du quartier Sainte-Thérèse. 1956-1985\n" .
                    "- Personnel de la mairie. — Attribution de la médaille du travail : liste des bénéficiaires. 1950-1960\n\n" .
                    "2) Intitulé à DEUX objets\n" .
                    "Structure (actions différentes) : Objet, action (dates) ; autre action (dates). Dates extrêmes\n" .
                    $ex .
                    "- Gymnase, construction (1958-1962) ; extension (1983). 1958-1983\n" .
                    "- Médecins, autorisations d'exercer : demandes, listes tenues à jour des autorisations accordées. an XI-1896\n" .
                    "Variante point-tiret : Objet. — Action (dates). Extension (dates). Dates extrêmes\n" .
                    $ex1 .
                    "- Gymnase. — Construction (1958-1962). Extension (1983). 1958-1983\n" .
                    "Structure (typologies différentes) : Objet, action : typologie, autre typologie. Dates extrêmes\n" .
                    $ex1 .
                    "- Sociétés de gymnastique, création : demandes de subvention, plans d'un stand de tir. 1887-1903\n\n" .
                    "3) Intitulé à TROIS objets ou plus\n" .
                    "Structure hiérarchisée : Objet principal. — Objet secondaire : typologie (dates). Autre objet secondaire : typologie (dates), autre typologie (dates). Dates extrêmes\n" .
                    $ex .
                    "- Géomètres du cadastre. — Effectifs : états nominatifs (1844). Rémunération : correspondance, décomptes des travaux faits, arrêtés préfectoraux fixant les indemnités, rapports (an XI-1869), états des sommes allouées (1810, 1844-1845). an XI-1869\n" .
                    "- Édifices communaux. — Mairie, reconstruction : plans (1880-1900), correspondance (1892-1899) ; extension : procès-verbal d'adjudication des travaux (1933). Écoles, aménagement : devis (par ordre alphabétique des entreprises, 1872-1930). 1872-1933\n\n" .
                    "Exemples d'usage des mots-outils :\n" .
                    "- Maison de la culture. — Construction, concerne aussi la rénovation du parking commun avec la médiathèque (1978-1986).\n" .
                    "- Débits de boissons. — Réglementation, concerne en particulier les horaires d'ouverture (1967-1973).\n" .
                    "- Gymnase. — Entretien (avec plans, 1987-1992).\n\n" .
                    "Contraintes de sortie : renvoyer uniquement le nouvel intitulé, sur une seule ligne, sans guillemets ni commentaire.",
                'is_system' => true,
            ],
            [
                'title' => 'action.reformulate_title.user',
                'content' =>
                    "Reformule l'intitulé archivistique ci-dessous en respectant strictement les règles suivantes (FR). N'invente rien ; conserve les dates existantes et positionne-les en fin comme dates extrêmes si pertinent.\n\n" .
                    "Règles et ponctuation :\n" .
                    "• Point-tiret (. —) recommandé pour séparer l'objet principal du reste.\n" .
                    "• Virgule : niveau équivalent ; point-virgule : éléments de même nature ; deux points : typologie ; point : clôt une sous-partie.\n" .
                    "• Mise en facteur commun : général → particulier ; éviter 'Idem'.\n\n" .
                    "1) UN objet — Structure : Objet. — Action : typologie documentaire. Dates extrêmes\n" .
                    $ex .
                    "- Rouen. — Aménagement du quartier Sainte-Thérèse. 1956-1985\n" .
                    "- Personnel de la mairie. — Attribution de la médaille du travail : liste des bénéficiaires. 1950-1960\n\n" .
                    "2) DEUX objets — Structures :\n" .
                    "- Objet, action (dates) ; autre action (dates). Dates extrêmes\n" .
                    "- Objet. — Action (dates). Extension (dates). Dates extrêmes\n" .
                    $ex .
                    "- Gymnase, construction (1958-1962) ; extension (1983). 1958-1983\n" .
                    "- Gymnase. — Construction (1958-1962). Extension (1983). 1958-1983\n" .
                    "- Médecins, autorisations d'exercer : demandes, listes tenues à jour des autorisations accordées. an XI-1896\n\n" .
                    "3) TROIS objets ou plus — Structure hiérarchisée : Objet principal. — Objet secondaire : typologie (dates). Autre objet secondaire : typologie (dates), autre typologie (dates). Dates extrêmes\n" .
                    $ex .
                    "- Géomètres du cadastre. — Effectifs : états nominatifs (1844). Rémunération : correspondance, décomptes des travaux faits, arrêtés préfectoraux fixant les indemnités, rapports (an XI-1869), états des sommes allouées (1810, 1844-1845). an XI-1869\n" .
                    "- Édifices communaux. — Mairie, reconstruction : plans (1880-1900), correspondance (1892-1899) ; extension : procès-verbal d'adjudication des travaux (1933). Écoles, aménagement : devis (par ordre alphabétique des entreprises, 1872-1930). 1872-1933\n\n" .
                    "Exemples d'usage :\n" .
                    "- Maison de la culture. — Construction, concerne aussi la rénovation du parking commun avec la médiathèque (1978-1986).\n" .
                    "- Débits de boissons. — Réglementation, concerne en particulier les horaires d'ouverture (1967-1973).\n" .
                    "- Gymnase. — Entretien (avec plans, 1987-1992).\n\n" .
                    "Contraintes de sortie : une seule ligne, claire et concise ; renvoyer uniquement le nouveau titre, sans guillemets ni commentaires.\n\n" .
                    "Intitulé d'origine :\n{{title}}",
                'is_system' => false,
            ],
        ];
    }

    private function promptsSummarize(string $fmt): array
    {
        return [
            [
                'title' => 'record_summarize',
                'content' =>
                    "Tu es un assistant archivistique.\n" .
                    "1) Résume le contenu des dossiers en 3 à 5 phrases (FR), en conservant les informations clés et le contexte administratif.\n" .
                    "2) Puis extrais 5 mots-clés, chacun accompagné de 3 synonymes en français.\n" .
                    "   Classe chaque mot-clé dans l'une des catégories suivantes : Personnalité (P), Matière (M), Énergie (E), Espace (E).\n" .
                    "   - Personnalité (P) : l'objet principal d'étude, son essence\n" .
                    "   - Matière (M) : composants, matériaux ou éléments constitutifs\n" .
                    "   - Énergie (E) : actions, processus ou fonctions liés au sujet\n" .
                    "   - Espace (E) : localisation géographique ou spatiale\n" .
                    $fmt .
                    "Résumé : <ton résumé>\n" .
                    "Mots-clés (5) :\n" .
                    "- [Catégorie] Mot-clé — synonymes : s1; s2; s3",
                'is_system' => true,
            ],
            [
                'title' => 'mail_summarize',
                'content' =>
                    "Tu es un assistant archivistique.\n" .
                    "1) Génère une description synthétique et professionnelle du courrier ci-dessous en 3 à 5 phrases (FR), en mettant en avant le contexte, l'objet, les parties prenantes, la typologie et les points saillants.\n" .
                    "2) Puis extrais 5 mots-clés, chacun accompagné de 3 synonymes en français.\n" .
                    "   Classe chaque mot-clé dans l'une des catégories suivantes : Personnalité (P), Matière (M), Énergie (E), Espace (E).\n" .
                    "   - Personnalité (P) : l'objet principal d'étude, son essence\n" .
                    "   - Matière (M) : composants, matériaux ou éléments constitutifs\n" .
                    "   - Énergie (E) : actions, processus ou fonctions liés au sujet\n" .
                    "   - Espace (E) : localisation géographique ou spatiale\n" .
                    $fmt .
                    "Résumé : <ta description>\n" .
                    "Mots-clés (5) :\n" .
                    "- [Catégorie] Mot-clé — synonymes : s1; s2; s3",
                'is_system' => true,
            ],
            [
                'title' => 'action.mail_summarize.user',
                'content' =>
                    "À partir du courrier électronique suivant, fournis :\n" .
                    "1) Une description synthétique et professionnelle en 3 à 5 phrases (FR), en mettant en avant le contexte, l'objet, les parties prenantes, la typologie et les points saillants.\n" .
                    "2) 5 mots-clés, chacun avec 3 synonymes (FR).\n" .
                    "   Catégorise chaque mot-clé parmi : Personnalité (P), Matière (M), Énergie (E), Espace (E).\n" .
                    "   - P : essence/objet principal ; M : composants/éléments ; E (Énergie) : actions/processus/fonctions ; E (Espace) : localisation.\n" .
                    "Contraintes : n'invente pas de faits ; reste fidèle au contenu du mail ; une ligne par mot-clé.\n\n" .
                    $fmt .
                    "Résumé : <ta description>\n" .
                    "Mots-clés (5) :\n" .
                    "- [Catégorie] Mot-clé — synonymes : s1; s2; s3\n\n" .
                    "Mail :\n" .
                    "De : {{from}}\n" .
                    "À : {{to}}\n" .
                    "Objet : {{subject}}\n" .
                    "Date : {{date}}\n" .
                    "Contenu :\n{{content}}",
                'is_system' => false,
            ],
            [
                'title' => 'action.summarize.user',
                'content' =>
                    "À partir du texte suivant, fournis :\n" .
                    "1) Un résumé en 3 à 5 phrases (FR), concis et informatif.\n" .
                    "2) 5 mots-clés, chacun avec 3 synonymes (FR).\n" .
                    "   Catégorise chaque mot-clé parmi : Personnalité (P), Matière (M), Énergie (E), Espace (E).\n" .
                    "   - P : essence/objet principal ; M : composants/éléments ; E (Énergie) : actions/processus/fonctions ; E (Espace) : localisation.\n" .
                    "Contraintes : n'invente pas de faits ; reste fidèle au texte ; une ligne par mot-clé.\n\n" .
                    $fmt .
                    "Résumé : <ton résumé>\n" .
                    "Mots-clés (5) :\n" .
                    "- [Catégorie] Mot-clé — synonymes : s1; s2; s3\n\n" .
                    "Texte :\n{{text}}",
                'is_system' => false,
            ],
        ];
    }

    private function promptsAssignThesaurus(string $fmt): array
    {
        return [
            [
                'title' => 'assign_thesaurus',
                'content' =>
                    "Tu aides à l'indexation avec un thésaurus.\n" .
                    "À partir d'un contenu fourni, propose 5 à 10 libellés préférentiels (FR) pertinents.\n" .
                    "Pour chaque libellé, fournis 1 à 3 synonymes (FR) utiles.\n" .
                    "Catégorise chaque proposition : P (Personnalité), M (Matière), En (Énergie), Es (Espace).\n" .
                    $fmt .
                    "- [Catégorie] Libellé — synonymes : s1; s2; s3",
                'is_system' => true,
            ],
            [
                'title' => 'action.assign_thesaurus.user',
                'content' =>
                    "À partir du contenu ci-dessous, propose 5 à 10 libellés préférentiels (FR) pertinents du thésaurus.\n" .
                    "Pour chaque ligne :\n" .
                    "- Indique une catégorie entre crochets : P (Personnalité), M (Matière), En (Énergie), Es (Espace).\n" .
                    "- Donne le libellé principal (prefLabel) puis 1 à 3 synonymes séparés par des points-virgules.\n" .
                    "- Évite les doublons et les termes trop généraux.\n\n" .
                    $fmt .
                    "- [Catégorie] Libellé — synonymes : s1; s2; s3\n\n" .
                    "Contenu :\n{{text}}",
                'is_system' => false,
            ],
        ];
    }

    private function promptsKeywords(): array
    {
        return [
            [
                'title' => 'record_keywords',
                'content' =>
                    "Tu es un assistant archivistique spécialisé dans l'extraction de mots-clés.\n" .
                    "À partir du contenu fourni (titre, description, notes, pièces jointes), extrais 5 à 15 mots-clés pertinents en français.\n" .
                    "Instructions :\n" .
                    "- Identifie les concepts clés, entités, sujets principaux\n" .
                    "- Inclus les noms propres, lieux, organisations, personnes\n" .
                    "- Ajoute les termes techniques ou spécialisés importants\n" .
                    "- Évite les mots vides (articles, prépositions, etc.)\n" .
                    "- Privilégie les termes d'indexation normalisés\n" .
                    "- Retourne uniquement un JSON valide avec un tableau 'keywords'\n\n" .
                    "Format de sortie :\n" .
                    "{\n" .
                    "  \"keywords\": [\n" .
                    "    {\"name\": \"Mot-clé1\", \"category\": \"Personnalité\"},\n" .
                    "    {\"name\": \"Mot-clé2\", \"category\": \"Matière\"},\n" .
                    "    {\"name\": \"Mot-clé3\", \"category\": \"Énergie\"},\n" .
                    "    {\"name\": \"Mot-clé4\", \"category\": \"Espace\"}\n" .
                    "  ]\n" .
                    "}\n\n" .
                    "Catégories :\n" .
                    "- Personnalité : personnes, entités principales, objets d'étude\n" .
                    "- Matière : documents, objets, matériaux, supports\n" .
                    "- Énergie : actions, processus, activités, fonctions\n" .
                    "- Espace : lieux, géographie, localisation, bâtiments",
                'is_system' => true,
            ],
        ];
    }

    private function seedSettings($now): void
    {
        // Seed AI global settings for default model/provider and API keys (optional fallbacks)
        $settings = [
            [
                'setting_key' => 'default_provider',
                'setting_value' => 'ollama',
                'setting_type' => 'string',
                'description' => 'Fournisseur AI par défaut',
                'is_encrypted' => false,
            ],
            [
                'setting_key' => 'default_model',
                'setting_value' => 'gemma3:4b',
                'setting_type' => 'string',
                'description' => 'Identifiant du modèle par défaut (ex: llama3, mistral, gpt-4o, claude-3)',
                'is_encrypted' => false,
            ],
            [
                'setting_key' => 'openai_api_key',
                'setting_value' => env('OPENAI_API_KEY', ''),
                'setting_type' => 'string',
                'description' => 'Clé API OpenAI (si utilisée)',
                'is_encrypted' => true,
            ],
            [
                'setting_key' => 'openai_custom_api_key',
                'setting_value' => env('OPENAI_CUSTOM_API_KEY', ''),
                'setting_type' => 'string',
                'description' => 'Clé API pour un endpoint OpenAI compatible custom',
                'is_encrypted' => true,
            ],
            [
                'setting_key' => 'openai_custom_base_url',
                'setting_value' => env('OPENAI_CUSTOM_BASE_URL', ''),
                'setting_type' => 'string',
                'description' => 'Base URL pour le endpoint OpenAI compatible custom',
                'is_encrypted' => false,
            ],
            [
                'setting_key' => 'openai_custom_paths',
                'setting_value' => [
                    'chat' => '/v1/chat/completions',
                    'embeddings' => '/v1/embeddings',
                    'image' => '/v1/images/generations',
                    'tts' => '/v1/audio/speech',
                    'stt' => '/v1/audio/transcriptions',
                ],
                'setting_type' => 'json',
                'description' => 'Chemins d’API pour le endpoint OpenAI compatible custom',
                'is_encrypted' => false,
            ],
            [
                'setting_key' => 'openai_custom_auth_header',
                'setting_value' => 'Authorization',
                'setting_type' => 'string',
                'description' => 'Nom de l’en-tête d’authentification pour OpenAI custom',
                'is_encrypted' => false,
            ],
            [
                'setting_key' => 'openai_custom_auth_prefix',
                'setting_value' => 'Bearer ',
                'setting_type' => 'string',
                'description' => 'Préfixe d’authentification pour OpenAI custom',
                'is_encrypted' => false,
            ],
            [
                'setting_key' => 'openai_custom_extra_headers',
                'setting_value' => [],
                'setting_type' => 'json',
                'description' => 'En-têtes additionnels pour OpenAI custom',
                'is_encrypted' => false,
            ],
            [
                'setting_key' => 'gemini_api_key',
                'setting_value' => env('GEMINI_API_KEY', ''),
                'setting_type' => 'string',
                'description' => 'Clé API Google Gemini (si utilisée)',
                'is_encrypted' => true,
            ],
            [
                'setting_key' => 'claude_api_key',
                'setting_value' => env('CLAUDE_API_KEY', ''),
                'setting_type' => 'string',
                'description' => 'Clé API Anthropic Claude (si utilisée)',
                'is_encrypted' => true,
            ],
            [
                'setting_key' => 'openrouter_api_key',
                'setting_value' => env('OPENROUTER_API_KEY', ''),
                'setting_type' => 'string',
                'description' => 'Clé API OpenRouter (si utilisée)',
                'is_encrypted' => true,
            ],
            [
                'setting_key' => 'openrouter_base_url',
                'setting_value' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
                'setting_type' => 'string',
                'description' => 'Base URL OpenRouter',
                'is_encrypted' => false,
            ],
            [
                'setting_key' => 'onn_api_key',
                'setting_value' => env('ONN_API_KEY', ''),
                'setting_type' => 'string',
                'description' => 'Clé API ONN (si utilisée)',
                'is_encrypted' => true,
            ],
            [
                'setting_key' => 'grok_api_key',
                'setting_value' => env('GROK_API_KEY', ''),
                'setting_type' => 'string',
                'description' => 'Clé API Grok (si utilisée)',
                'is_encrypted' => true,
            ],
            [
                'setting_key' => 'ollama_turbo_api_key',
                'setting_value' => env('OLLAMA_TURBO_API_KEY', ''),
                'setting_type' => 'string',
                'description' => 'Clé API Ollama Turbo (si utilisée)',
                'is_encrypted' => true,
            ],
            [
                'setting_key' => 'ollama_turbo_endpoint',
                'setting_value' => env('OLLAMA_TURBO_ENDPOINT', 'https://ollama.com'),
                'setting_type' => 'string',
                'description' => 'Endpoint Ollama Turbo',
                'is_encrypted' => false,
            ],
        ];

        foreach ($settings as $s) {
            $value = $s['setting_value'];
            // Normalize to string before optional encryption
            $normalized = is_string($value) ? $value : json_encode($value);
            if (!empty($normalized) && ($s['is_encrypted'] ?? false)) {
                try { $normalized = Crypt::encryptString($normalized); } catch (\Throwable) { /* ignore encryption failure */ }
            }
            DB::table('ai_global_settings')->updateOrInsert(
                ['setting_key' => $s['setting_key']],
                [
                    'setting_value' => $normalized,
                    'setting_type' => $s['setting_type'],
                    'description' => $s['description'],
                    'is_encrypted' => $s['is_encrypted'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
