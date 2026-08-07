<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AI\QueryAnalyzerService;
use App\Services\AI\QueryExecutorService;
use App\Services\AI\ResponseFormatterService;
use Illuminate\Support\Facades\Log;

class AiSearchTestController extends Controller
{
    private QueryAnalyzerService $analyzer;
    private QueryExecutorService $executor;
    private ResponseFormatterService $formatter;

    public function __construct(
        QueryAnalyzerService $analyzer,
        QueryExecutorService $executor,
        ResponseFormatterService $formatter
    ) {
        $this->analyzer = $analyzer;
        $this->executor = $executor;
        $this->formatter = $formatter;
    }

    /**
     * Exécute une série de tests automatisés pour valider le système de recherche IA
     */
    public function runTests()
    {
        $testCases = $this->getTestCases();
        $results = [];

        foreach ($testCases as $testCase) {
            $result = $this->runSingleTest($testCase);
            $results[] = $result;
        }

        return response()->json([
            'success' => true,
            'total_tests' => count($testCases),
            'passed' => count(array_filter($results, fn($r) => $r['passed'])),
            'failed' => count(array_filter($results, fn($r) => !$r['passed'])),
            'results' => $results
        ]);
    }

    /**
     * Exécute un test spécifique
     */
    public function runTest($testName)
    {
        $testCases = $this->getTestCases();
        $testCase = array_values(array_filter($testCases, fn($t) => $t['name'] === $testName))[0] ?? null;

        if (!$testCase) {
            return response()->json([
                'success' => false,
                'error' => 'Test case not found'
            ], 404);
        }

        $result = $this->runSingleTest($testCase);
        return response()->json($result);
    }

    /**
     * Retourne la liste des cas de test disponibles
     */
    public function getTestCases()
    {
        return [
            // ===== TESTS GÉNÉRAUX =====
            [
                'name' => 'test_count_all_records',
                'description' => 'Tester le comptage de tous les documents',
                'query' => 'combien de documents',
                'expected_action' => 'count',
                'expected_filters' => [],
                'search_type' => 'records'
            ],
            [
                'name' => 'test_count_by_year',
                'description' => 'Tester le comptage par année',
                'query' => 'combien de documents en 2024',
                'expected_action' => 'count',
                'expected_filters' => ['year' => 2024],
                'search_type' => 'records'
            ],
            [
                'name' => 'test_count_today',
                'description' => 'Tester le comptage d\'aujourd\'hui',
                'query' => 'combien d\'éléments ajoutés aujourd\'hui',
                'expected_action' => 'count',
                'expected_filters' => ['date_from' => date('Y-m-d'), 'date_to' => date('Y-m-d')],
                'search_type' => 'records'
            ],

            // ===== TESTS RECORDS AVANCÉS =====
            [
                'name' => 'test_records_by_author',
                'description' => 'Tester la recherche par auteur dans les documents',
                'query' => 'documents de Martin Dubois',
                'expected_action' => 'filter',
                'expected_filters' => ['author' => 'Martin'],
                'search_type' => 'records'
            ],
            [
                'name' => 'test_records_by_activity',
                'description' => 'Tester la recherche par activité',
                'query' => 'archives de l\'activité juridique',
                'expected_action' => 'filter',
                'expected_filters' => ['activity' => 'juridique'],
                'search_type' => 'records'
            ],
            [
                'name' => 'test_records_by_container',
                'description' => 'Tester la recherche par conteneur',
                'query' => 'documents dans le conteneur A123',
                'expected_action' => 'filter',
                'expected_filters' => ['container' => 'A123'],
                'search_type' => 'records'
            ],
            [
                'name' => 'test_records_by_term',
                'description' => 'Tester la recherche par terme indexé',
                'query' => 'documents avec le terme contrat',
                'expected_action' => 'filter',
                'expected_filters' => ['term' => 'contrat'],
                'search_type' => 'records'
            ],

            // ===== TESTS MAILS AVANCÉS =====
            [
                'name' => 'test_mails_urgent',
                'description' => 'Tester la recherche de mails urgents',
                'query' => 'courriers urgents',
                'expected_action' => 'filter',
                'expected_filters' => ['priority' => 'urgent'],
                'search_type' => 'mails'
            ],
            [
                'name' => 'test_mails_by_type',
                'description' => 'Tester la recherche par type de mail',
                'query' => 'mails entrants',
                'expected_action' => 'filter',
                'expected_filters' => ['mail_type' => 'entrant'],
                'search_type' => 'mails'
            ],
            [
                'name' => 'test_mails_administrative',
                'description' => 'Tester la recherche par typologie administrative',
                'query' => 'courriers administratifs',
                'expected_action' => 'filter',
                'expected_filters' => ['typology' => 'administratif'],
                'search_type' => 'mails'
            ],
            [
                'name' => 'test_mails_with_attachments',
                'description' => 'Tester la recherche dans les pièces jointes',
                'query' => 'emails avec pièces jointes PDF',
                'expected_action' => 'search',
                'expected_keywords' => ['PDF'],
                'expected_fields' => ['attachment_content'],
                'search_type' => 'mails'
            ],
            [
                'name' => 'test_mails_received_date',
                'description' => 'Tester la recherche par date de réception',
                'query' => 'mails reçus hier',
                'expected_action' => 'filter',
                'expected_filters' => ['date_from' => date('Y-m-d', strtotime('-1 day')), 'date_to' => date('Y-m-d', strtotime('-1 day'))],
                'search_type' => 'mails'
            ],

            // ===== TESTS COMMUNICATIONS AVANCÉS =====
            [
                'name' => 'test_communications_by_operator',
                'description' => 'Tester la recherche par opérateur',
                'query' => 'communications de Dupont',
                'expected_action' => 'filter',
                'expected_filters' => ['operator' => 'Dupont'],
                'search_type' => 'communications'
            ],
            [
                'name' => 'test_communications_in_progress',
                'description' => 'Tester la recherche des communications en cours',
                'query' => 'communications en cours',
                'expected_action' => 'filter',
                'expected_filters' => ['status' => 'en cours'],
                'search_type' => 'communications'
            ],
            [
                'name' => 'test_communications_return_date',
                'description' => 'Tester la recherche par date de retour',
                'query' => 'retours prévus pour janvier 2025',
                'expected_action' => 'filter',
                'expected_filters' => ['date_from' => '2025-01-01', 'date_to' => '2025-01-31'],
                'search_type' => 'communications'
            ],

            // ===== TESTS SLIPS AVANCÉS =====
            [
                'name' => 'test_slips_approved',
                'description' => 'Tester la recherche des bordereaux approuvés',
                'query' => 'bordereaux approuvés',
                'expected_action' => 'filter',
                'expected_filters' => ['slip_status' => 'approuvé'],
                'search_type' => 'slips'
            ],
            [
                'name' => 'test_slips_by_officer',
                'description' => 'Tester la recherche par agent',
                'query' => 'transferts traités par Agent123',
                'expected_action' => 'filter',
                'expected_filters' => ['officer' => 'Agent123'],
                'search_type' => 'slips'
            ],
            [
                'name' => 'test_slips_integrated_this_week',
                'description' => 'Tester la recherche des intégrations de cette semaine',
                'query' => 'bordereaux intégrés cette semaine',
                'expected_action' => 'filter',
                'expected_filters' => ['slip_status' => 'intégré', 'date_from' => date('Y-m-d', strtotime('monday this week')), 'date_to' => date('Y-m-d')],
                'search_type' => 'slips'
            ],

            // ===== TESTS AVANCÉS COMPLEXES =====
            [
                'name' => 'test_advanced_multi_filter',
                'description' => 'Tester une recherche avancée multi-critères',
                'query' => 'documents urgents de Martin en 2024 dans conteneur A123',
                'expected_action' => 'advanced',
                'expected_filters' => ['author' => 'Martin', 'year' => 2024, 'container' => 'A123', 'priority' => 'urgent'],
                'search_type' => 'records'
            ],
            [
                'name' => 'test_date_range',
                'description' => 'Tester la recherche par plage de dates',
                'query' => 'documents entre mars et juin 2024',
                'expected_action' => 'date_range',
                'expected_filters' => ['date_from' => '2024-03-01', 'date_to' => '2024-06-30'],
                'search_type' => 'records'
            ],
            [
                'name' => 'test_show_specific',
                'description' => 'Tester l\'affichage d\'un élément spécifique',
                'query' => 'document numéro 25',
                'expected_action' => 'show',
                'expected_id' => 25,
                'search_type' => 'records'
            ],
            [
                'name' => 'test_list_recent',
                'description' => 'Tester la liste des éléments récents',
                'query' => 'les 5 derniers documents',
                'expected_action' => 'list',
                'expected_limit' => 5,
                'expected_order' => 'desc',
                'search_type' => 'records'
            ]
        ];
    }

    /**
     * Exécute un seul test
     */
    private function runSingleTest($testCase)
    {
        try {
            // Étape 1: Analyser la requête
            $instructions = $this->analyzer->analyzeQuery($testCase['query'], $testCase['search_type']);

            $testResult = [
                'name' => $testCase['name'],
                'description' => $testCase['description'],
                'query' => $testCase['query'],
                'search_type' => $testCase['search_type'],
                'passed' => true,
                'errors' => [],
                'analysis_result' => $instructions,
                'execution_result' => null,
                'format_result' => null
            ];

            // Vérifier l'analyse
            if (!$instructions['success']) {
                $testResult['passed'] = false;
                $testResult['errors'][] = "Analysis failed: " . ($instructions['error'] ?? 'Unknown error');
                return $testResult;
            }

            // Vérifier l'action
            if (isset($testCase['expected_action']) && $instructions['action'] !== $testCase['expected_action']) {
                $testResult['passed'] = false;
                $testResult['errors'][] = "Expected action '{$testCase['expected_action']}', got '{$instructions['action']}'";
            }

            // Vérifier les filtres
            if (isset($testCase['expected_filters'])) {
                $actualFilters = $instructions['filters'] ?? [];
                foreach ($testCase['expected_filters'] as $key => $expectedValue) {
                    if (!isset($actualFilters[$key]) || $actualFilters[$key] != $expectedValue) {
                        $testResult['passed'] = false;
                        $testResult['errors'][] = "Expected filter '{$key}' = '{$expectedValue}', got '" . ($actualFilters[$key] ?? 'null') . "'";
                    }
                }
            }

            // Vérifier les mots-clés
            if (isset($testCase['expected_keywords'])) {
                $actualKeywords = $instructions['keywords'] ?? [];
                foreach ($testCase['expected_keywords'] as $expectedKeyword) {
                    if (!in_array(strtolower($expectedKeyword), array_map('strtolower', $actualKeywords))) {
                        $testResult['passed'] = false;
                        $testResult['errors'][] = "Expected keyword '{$expectedKeyword}' not found in: " . implode(', ', $actualKeywords);
                    }
                }
            }

            // Vérifier la limite
            if (isset($testCase['expected_limit']) && $instructions['limit'] != $testCase['expected_limit']) {
                $testResult['passed'] = false;
                $testResult['errors'][] = "Expected limit '{$testCase['expected_limit']}', got '{$instructions['limit']}'";
            }

            // Vérifier l'ordre
            if (isset($testCase['expected_order']) && $instructions['order'] != $testCase['expected_order']) {
                $testResult['passed'] = false;
                $testResult['errors'][] = "Expected order '{$testCase['expected_order']}', got '{$instructions['order']}'";
            }

            // Vérifier l'ID
            if (isset($testCase['expected_id']) && $instructions['id'] != $testCase['expected_id']) {
                $testResult['passed'] = false;
                $testResult['errors'][] = "Expected ID '{$testCase['expected_id']}', got '{$instructions['id']}'";
            }

            // Étape 2: Exécuter la requête (si l'analyse a réussi)
            if ($testResult['passed']) {
                try {
                    $executionResult = $this->executor->executeQuery($instructions);
                    $testResult['execution_result'] = $executionResult;

                    if (!$executionResult['success']) {
                        $testResult['passed'] = false;
                        $testResult['errors'][] = "Execution failed: " . ($executionResult['error'] ?? 'Unknown execution error');
                    }

                    // Étape 3: Formatter la réponse
                    if ($executionResult['success']) {
                        $formatResult = $this->formatter->formatResponse($executionResult, $testCase['search_type']);
                        $testResult['format_result'] = $formatResult;

                        if (!$formatResult['success']) {
                            $testResult['passed'] = false;
                            $testResult['errors'][] = "Formatting failed";
                        }
                    }
                } catch (\Exception $e) {
                    $testResult['passed'] = false;
                    $testResult['errors'][] = "Execution exception: " . $e->getMessage();
                }
            }

            return $testResult;

        } catch (\Exception $e) {
            return [
                'name' => $testCase['name'],
                'description' => $testCase['description'],
                'query' => $testCase['query'],
                'search_type' => $testCase['search_type'],
                'passed' => false,
                'errors' => ["Test exception: " . $e->getMessage()],
                'analysis_result' => null,
                'execution_result' => null,
                'format_result' => null
            ];
        }
    }

    /**
     * Interface web pour exécuter les tests
     */
    public function testInterface()
    {
        return view('ai-search.test-interface', [
            'test_cases' => $this->getTestCases()
        ]);
    }
}