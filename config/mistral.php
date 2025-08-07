<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mistral API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour l'API Mistral AI utilisée pour tester
    | l'intégration avec les fonctionnalités MCP.
    |
    */

    'api_key' => env('MISTRAL_API_KEY', null),

    'base_url' => env('MISTRAL_BASE_URL', 'https://api.mistral.ai'),

    'timeout' => env('MISTRAL_TIMEOUT', 30),

    'max_retries' => env('MISTRAL_MAX_RETRIES', 3),

    /*
    |--------------------------------------------------------------------------
    | Default Models
    |--------------------------------------------------------------------------
    |
    | Modèles par défaut pour chaque type de tâche
    |
    */
    'models' => [
        'title_reformulation' => env('MISTRAL_MODEL_TITLE', 'mistral-medium-latest'),
        'thesaurus_indexing' => env('MISTRAL_MODEL_THESAURUS', 'mistral-large-latest'),
        'summary_generation' => env('MISTRAL_MODEL_SUMMARY', 'mistral-large-latest'),
        'general' => env('MISTRAL_MODEL_GENERAL', 'mistral-medium-latest'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Parameters
    |--------------------------------------------------------------------------
    |
    | Paramètres par défaut pour les requêtes
    |
    */
    'defaults' => [
        'temperature' => 0.3,
        'max_tokens' => 1000,
        'safe_mode' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Task-specific Parameters
    |--------------------------------------------------------------------------
    |
    | Paramètres spécifiques par type de tâche
    |
    */
    'tasks' => [
        'title_reformulation' => [
            'temperature' => 0.3,
            'max_tokens' => 200,
        ],
        'thesaurus_indexing' => [
            'temperature' => 0.2,
            'max_tokens' => 500,
        ],
        'summary_generation' => [
            'temperature' => 0.4,
            'max_tokens' => 800,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configuration des limites de taux
    |
    */
    'rate_limit' => [
        'requests_per_minute' => env('MISTRAL_RATE_LIMIT', 100),
        'tokens_per_minute' => env('MISTRAL_TOKEN_LIMIT', 500000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Configuration des logs pour Mistral
    |
    */
    'logging' => [
        'enabled' => env('MISTRAL_LOGGING_ENABLED', true),
        'level' => env('MISTRAL_LOGGING_LEVEL', 'info'),
        'log_requests' => env('MISTRAL_LOG_REQUESTS', false),
        'log_responses' => env('MISTRAL_LOG_RESPONSES', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Test Mode
    |--------------------------------------------------------------------------
    |
    | Configuration pour le mode test
    |
    */
    'test_mode' => [
        'enabled' => env('MISTRAL_TEST_MODE', false),
        'mock_responses' => env('MISTRAL_MOCK_RESPONSES', false),
    ],
];