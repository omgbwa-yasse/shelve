<?php

// Config for Cloudstudio/Ollama

return [
    // Align default model with project-wide default (gemma3:4b)
    'model' => env('OLLAMA_MODEL', 'gemma3:4b'),
    'url' => env('OLLAMA_URL', 'http://127.0.0.1:11434'),
    'default_prompt' => env('OLLAMA_DEFAULT_PROMPT', 'Hello, how can I assist you today?'),
    'connection' => [
        'timeout' => env('OLLAMA_CONNECTION_TIMEOUT', 300),
    ],
    // Only set Authorization header if an API key is provided
    'headers' => env('OLLAMA_API_KEY')
        ? [ 'Authorization' => 'Bearer ' . env('OLLAMA_API_KEY') ]
        : [],
];
