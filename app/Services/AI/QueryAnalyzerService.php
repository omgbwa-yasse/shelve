<?php

namespace App\Services\AI;

use AiBridge\Facades\AiBridge;
use App\Services\AI\ProviderRegistry;

class QueryAnalyzerService
{
    private ProviderRegistry $registry;

    public function __construct(ProviderRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function analyzeQuery(string $userQuery, string $searchType = 'records'): array
    {
        // Utiliser le provider par défaut depuis les paramètres
        $defaultProvider = $this->registry->getSetting('ai_default_provider', 'mistral');
        if (is_string($defaultProvider)) {
            $providerName = json_decode($defaultProvider, true) ?: $defaultProvider;
        } else {
            $providerName = $defaultProvider;
        }

        $this->registry->ensureConfigured($providerName);

        $analysisPrompt = $this->getAnalysisPrompt($searchType);

        $messages = [
            ['role' => 'system', 'content' => $analysisPrompt],
            ['role' => 'user', 'content' => $userQuery]
        ];

        try {
            // Obtenir le modèle par défaut pour le provider
            $defaultModel = $this->getDefaultModelForProvider($providerName);

            $response = AiBridge::provider($providerName)->chat($messages, [
                'model' => $defaultModel
            ]);

            $aiResponse = $this->extractAIResponse($response);
            return $this->parseAIInstructions($aiResponse);

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Erreur IA: ' . $e->getMessage(),
                'action' => 'error'
            ];
        }
    }

    private function getDefaultModelForProvider(string $providerName): string
    {
        return match($providerName) {
            'mistral' => $this->registry->getSetting('mistral_default_model', 'mistral-large-latest'),
            'claude' => 'claude-3-5-sonnet-20241022',
            'openai' => 'gpt-4',
            'ollama' => $this->registry->getSetting('ollama_default_model', 'llama3.1'),
            default => 'mistral-large-latest'
        };
    }

    private function getAnalysisPrompt(string $searchType): string
    {
        $now = now();
        $today = $now->toDateString();
        $yesterday = $now->copy()->subDay()->toDateString();
        $weekStart = $now->copy()->startOfWeek()->toDateString();
        $weekEnd = $now->copy()->endOfWeek()->toDateString();
        $monthName = $now->locale('fr')->translatedFormat('F Y');
        $month = $now->month;
        $year = $now->year;

        return "Tu es un analyseur de requêtes intelligent spécialisé dans les archives. Tu analyses les demandes utilisateur et retournes des instructions JSON précises pour Laravel.

TYPES D'ACTIONS DISPONIBLES:
1. \"search\" - Recherche par mots-clés dans le contenu
2. \"count\" - Compter des éléments UNIQUEMENT si l'utilisateur demande explicitement \"combien\"
3. \"filter\" - Filtrer par critères spécifiques (année, mois, auteur, type, etc.) ET AFFICHER LA LISTE
4. \"list\" - Lister les éléments récents ou tous
5. \"show\" - Afficher un élément spécifique par ID
6. \"date_range\" - Recherche dans une plage de dates spécifique ET AFFICHER LA LISTE
7. \"advanced\" - Recherche avancée avec critères complexes multiples

RÈGLE IMPORTANTE:
- Utilisez \"count\" SEULEMENT si l'utilisateur demande explicitement \"combien\" sans vouloir voir la liste
- Dans TOUS LES AUTRES CAS, utilisez \"filter\" ou \"date_range\" pour montrer les résultats à l'utilisateur

FORMAT DE RÉPONSE JSON OBLIGATOIRE:
{
  \"action\": \"type_action\",
  \"keywords\": [\"mot1\", \"mot2\"],
  \"filters\": {
    \"year\": 2024,
    \"month\": 9,
    \"date_from\": \"2024-01-01\",
    \"date_to\": \"2024-12-31\",
    \"author\": \"nom_auteur\",
    \"activity\": \"activite\",
    \"status\": \"active\",
    \"priority\": \"urgent\",
    \"type\": \"accord\",
    \"container\": \"conteneur_123\"
  },
  \"fields\": [\"name\", \"archivist_note\", \"content\"],
  \"limit\": 10,
  \"order\": \"desc\",
  \"table\": \"$searchType\"
}

═══ CHAMPS DISPONIBLES PAR TYPE ═══

🗂️ POUR \"records\" (Documents/Archives):
CHAMPS: code, name, archivist_note, content, created_at, date_start, date_end, date_exact
FILTRES: author (nom/prénom), activity (nom activité), status (nom statut), term (mots-clés),
         container (nom conteneur), shelf (étagère), room (salle), creator (créateur),
         dua (délai communicabilité), dul (délai légal)
DATES: created_at, date_start, date_end, date_exact, date_creation

📧 POUR \"mails\" (Courriers):
CHAMPS: code, name (objet), content, created_at, received_date
FILTRES: author (expéditeur/destinataire), priority (urgent/normal/faible),
         mail_type (entrant/sortant), typology (administrative/technique),
         document_type (lettre/email/fax), container, attachment_content
DATES: created_at, received_date, date (date du mail)

💬 POUR \"communications\" (Communications):
CHAMPS: code, name, content, created_at, return_date, return_effective
FILTRES: status (en cours/terminé/annulé), operator (nom opérateur),
         user (nom utilisateur), operator_organisation, user_organisation
DATES: created_at, return_date, return_effective

📋 POUR \"slips\" (Bordereaux/Transferts):
CHAMPS: code, name, description, created_at, received_date, approved_date, integrated_date
FILTRES: slip_status (reçu/approuvé/intégré), officer (agent), user (utilisateur),
         officer_organisation, user_organisation, container, record (référence archive)
DATES: created_at, received_date, approved_date, integrated_date

👤 POUR \"authors\" (Auteurs):
CHAMPS: id, name, parallel_name, other_name, lifespan, locations, created_at, updated_at
FILTRES: name (nom de l'auteur), type (type d'auteur), parent (auteur parent)
DATES: created_at, updated_at

═══ EXEMPLES COMPLETS PAR TYPE ═══

📋 RECORDS:
\"documents urgents de Martin en 2024\" → {\"action\":\"filter\",\"filters\":{\"author\":\"Martin\",\"year\":2024,\"priority\":\"urgent\"},\"table\":\"records\"}
\"archives dans le conteneur A123\" → {\"action\":\"filter\",\"filters\":{\"container\":\"A123\"},\"table\":\"records\"}
\"documents avec terme juridique\" → {\"action\":\"filter\",\"filters\":{\"term\":\"juridique\"},\"table\":\"records\"}

📧 MAILS:
\"mails urgents reçus hier\" → {\"action\":\"filter\",\"filters\":{\"priority\":\"urgent\",\"date_from\":\"{$yesterday}\",\"date_to\":\"{$yesterday}\"},\"table\":\"mails\"}
\"courriers de type administratif\" → {\"action\":\"filter\",\"filters\":{\"typology\":\"administratif\"},\"table\":\"mails\"}
\"emails avec pièces jointes PDF\" → {\"action\":\"search\",\"keywords\":[\"PDF\"],\"fields\":[\"attachment_content\"],\"table\":\"mails\"}

💬 COMMUNICATIONS:
\"communications en cours par Dupont\" → {\"action\":\"filter\",\"filters\":{\"status\":\"en cours\",\"operator\":\"Dupont\"},\"table\":\"communications\"}
\"retours prévus pour janvier 2025\" → {\"action\":\"filter\",\"filters\":{\"date_from\":\"2025-01-01\",\"date_to\":\"2025-01-31\"},\"fields\":[\"return_date\"],\"table\":\"communications\"}

📋 SLIPS:
\"bordereaux approuvés par Admin\" → {\"action\":\"filter\",\"filters\":{\"slip_status\":\"approuvé\",\"approved_by\":\"Admin\"},\"table\":\"slips\"}
\"transferts intégrés cette semaine\" → {\"action\":\"filter\",\"filters\":{\"slip_status\":\"intégré\",\"date_from\":\"{$weekStart}\",\"date_to\":\"{$weekEnd}\"},\"table\":\"slips\"}

👤 AUTHORS:
\"liste des auteurs\" → {\"action\":\"list\",\"table\":\"authors\",\"order\":\"asc\",\"order_by\":\"name\"}
\"auteurs par nom\" → {\"action\":\"filter\",\"filters\":{\"name\":\"Martin\"},\"table\":\"authors\"}
\"tous les auteurs\" → {\"action\":\"list\",\"table\":\"authors\",\"limit\":50}

GESTION INTELLIGENTE DES DATES:
- \"aujourd'hui\" = {$today}
- \"hier\" = {$yesterday}
- \"cette semaine\" = {$weekStart} to {$weekEnd}
- \"ce mois\" = {$monthName} (month: {$month}, year: {$year})
- \"cette année\" = {$year}
- \"janvier\", \"février\", etc. = month: 1, 2, etc.
- \"2024\", \"2023\" = year spécifique

FILTRES INTELLIGENTS:
- \"urgent\" → priority: \"urgent\" (mails)
- \"en cours\" → status: \"en cours\" (communications/slips)
- \"approuvé\" → status/slip_status: \"approuvé\"
- \"Martin\" → author: \"Martin\" (cherche nom/prénom)
- \"conteneur A123\" → container: \"A123\"

EXEMPLES DE DISTINCTION COUNT VS FILTER:
\"combien d'éléments en 2024\" → {\"action\":\"count\",\"filters\":{\"year\":2024}}
\"éléments de 2024\" → {\"action\":\"filter\",\"filters\":{\"year\":2024}}
\"montrez-moi les documents de 2024\" → {\"action\":\"filter\",\"filters\":{\"year\":2024}}
\"liste des archives de janvier\" → {\"action\":\"filter\",\"filters\":{\"month\":1}}

IMPORTANT:
- Retourne UNIQUEMENT du JSON valide
- Adapte les filtres selon le type ($searchType)
- Utilise les bons noms de champs pour chaque table
- Gère les relations complexes (auteur, organisations, etc.)
- \"count\" = nombre seulement, \"filter\"/\"date_range\" = liste des résultats
- Limite par défaut: 10 pour search/filter, 20 pour date_range";
    }

    private function extractAIResponse($response)
    {
        return ResponseTextExtractor::extract($response);
    }

    private function parseAIInstructions(string $aiResponse): array
    {
        // Nettoyer la réponse pour extraire le JSON
        $cleanResponse = trim($aiResponse);

        // Supprimer les balises markdown si présentes
        $cleanResponse = preg_replace('/^```json\s*/', '', $cleanResponse);
        $cleanResponse = preg_replace('/\s*```$/', '', $cleanResponse);

        try {
            $instructions = json_decode($cleanResponse, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'error' => 'Réponse IA invalide: ' . json_last_error_msg(),
                    'action' => 'error'
                ];
            }

            // Validation des champs obligatoires
            if (!isset($instructions['action'])) {
                return [
                    'success' => false,
                    'error' => 'Action manquante dans la réponse IA',
                    'action' => 'error'
                ];
            }

            $instructions['success'] = true;
            return $instructions;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Erreur parsing JSON: ' . $e->getMessage(),
                'action' => 'error'
            ];
        }
    }
}