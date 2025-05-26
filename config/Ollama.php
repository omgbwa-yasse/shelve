<?php

// config/ollama.php
return [
    /*
    |--------------------------------------------------------------------------
    | Ollama Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour l'intégration avec Ollama
    |
    */

    'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),

    'timeout' => env('OLLAMA_TIMEOUT', 120),

    'default_options' => [
        'temperature' => env('OLLAMA_TEMPERATURE', 0.7),
        'top_p' => env('OLLAMA_TOP_P', 0.9),
        'top_k' => env('OLLAMA_TOP_K', 40),
        'repeat_penalty' => env('OLLAMA_REPEAT_PENALTY', 1.1),
    ],

    'models' => [
        'auto_sync' => env('OLLAMA_AUTO_SYNC', true),
        'sync_interval' => env('OLLAMA_SYNC_INTERVAL', 3600), // seconds
    ],

    'streaming' => [
        'enabled' => env('OLLAMA_STREAMING_ENABLED', true),
        'chunk_size' => env('OLLAMA_CHUNK_SIZE', 1024),
    ],

    'queue' => [
        'enabled' => env('OLLAMA_QUEUE_ENABLED', true),
        'connection' => env('OLLAMA_QUEUE_CONNECTION', 'default'),
        'timeout' => env('OLLAMA_QUEUE_TIMEOUT', 300),
    ],
];