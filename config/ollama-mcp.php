<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration MCP (Model Context Protocol) pour Ollama
    |--------------------------------------------------------------------------
    |
    | Configuration spécialisée pour les fonctionnalités MCP avec Ollama
    | dans le système d'archivage selon les règles ISAD(G)
    |
    */

    'base_url' => env('OLLAMA_URL', 'http://127.0.0.1:11434'),

    'timeout' => env('AI_REQUEST_TIMEOUT', env('OLLAMA_CONNECTION_TIMEOUT', 300)),

    'models' => [
        'title_reformulation' => env('OLLAMA_MCP_TITLE_MODEL', 'gemma3:4b'),
        'thesaurus_indexing' => env('OLLAMA_MCP_THESAURUS_MODEL', 'gemma3:4b'),
        'content_summarization' => env('OLLAMA_MCP_SUMMARY_MODEL', 'gemma3:4b'),
        'keyword_extraction' => env('OLLAMA_MCP_KEYWORD_MODEL', 'gemma3:4b'),
    ],

    'options' => [
        'temperature' => env('OLLAMA_MCP_TEMPERATURE', 0.2), // Plus déterministe pour l'archivage
        'top_p' => env('OLLAMA_MCP_TOP_P', 0.9),
        'max_tokens' => env('OLLAMA_MCP_MAX_TOKENS', 2000),
        'stop' => ['\n\n', 'Explication:', 'Note:'], // Mots d'arrêt pour les réponses
    ],

    'features' => [
        'auto_process_on_create' => env('MCP_AUTO_PROCESS_CREATE', true),
        'auto_process_on_update' => env('MCP_AUTO_PROCESS_UPDATE', false),
        'auto_features_on_create' => ['thesaurus'], // Plus léger à la création
        'auto_features_on_update' => ['summary'], // Mise à jour du résumé seulement
    ],

    'performance' => [
        'cache_responses' => env('MCP_CACHE_RESPONSES', true),
        'cache_ttl' => env('MCP_CACHE_TTL', 3600), // 1 heure
        'batch_size' => env('MCP_BATCH_SIZE', 10),
        'delay_between_requests' => env('MCP_DELAY_MS', 100), // ms
        'max_retries' => env('MCP_MAX_RETRIES', 3),
        'retry_delay' => env('MCP_RETRY_DELAY', 1000), // ms
    ],

    'validation' => [
        'min_content_length' => 50, // Longueur minimum pour traiter
        'max_content_length' => 10000, // Limite pour éviter les timeouts
        'required_fields' => ['name'], // Champs requis dans Record
    ],

    'prompts' => [
        'system_prompt' => 'Vous êtes un archiviste expert spécialisé dans les règles ISAD(G) et la description documentaire.',
        'title_prompt_prefix' => 'Reformulez ce titre d\'archive selon les règles ISAD(G) :',
        'thesaurus_prompt_prefix' => 'Analysez ce texte archivistique et extrayez les mots-clés principaux :',
        'summary_prompt_prefix' => 'Rédigez une description de contenu selon la norme ISAD(G) élément 3.3.1 "Portée et contenu" :',
    ],

    'rate_limiting' => [
        'enabled' => env('MCP_RATE_LIMIT_ENABLED', true),
        'requests_per_minute' => env('MCP_RATE_LIMIT_REQUESTS', 60),
        'requests_per_hour' => env('MCP_RATE_LIMIT_REQUESTS_HOUR', 1000),
    ],

    'logging' => [
        'enabled' => env('MCP_LOGGING_ENABLED', true),
        'log_requests' => env('MCP_LOG_REQUESTS', true),
        'log_responses' => env('MCP_LOG_RESPONSES', false), // Peut être volumineux
        'log_errors' => env('MCP_LOG_ERRORS', true),
    ],
];
