<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MCP Server Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour le serveur MCP (Model Context Protocol)
    | utilisé pour l'analyse de documents avec l'IA
    |
    */

    'base_url' => env('MCP_BASE_URL', 'http://localhost:3000'),

    'default_model' => env('MCP_DEFAULT_MODEL', 'llama3'),

    'timeout' => env('MCP_TIMEOUT', 120),

    'api_token' => env('MCP_API_TOKEN'),

    'max_documents_per_analysis' => env('MCP_MAX_DOCUMENTS', 20),

    'supported_formats' => [
        'pdf', 'txt', 'docx', 'doc', 'rtf', 'odt'
    ],

    /*
    |--------------------------------------------------------------------------
    | Analysis Options
    |--------------------------------------------------------------------------
    */

    'analysis' => [
        'default_template' => 'detailed',
        'default_max_terms' => 15,
        'default_weighting_method' => 'combined',
        'confidence_thresholds' => [
            'high' => 0.7,
            'medium' => 0.4,
            'low' => 0.0
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Record Mapping
    |--------------------------------------------------------------------------
    */

    'record_mapping' => [
        'support_ids' => [
            'numérique' => 1,
            'papier' => 2,
            'mixte' => 3
        ],
        'level_ids' => [
            'fonds' => 1,
            'series' => 2,
            'file' => 3,
            'item' => 4
        ]
    ]
];
