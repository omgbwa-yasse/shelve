<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use AiBridge\Facades\AiBridge;
use AiBridge\Providers\OpenAIProvider;
use AiBridge\Providers\GeminiProvider;
use AiBridge\Providers\ClaudeProvider;
use AiBridge\Providers\CustomOpenAIProvider;
use AiBridge\Providers\OnnProvider;
use AiBridge\Providers\GrokProvider;
use AiBridge\Providers\OllamaTurboProvider;

class ProviderRegistry
{
    private const AUTH_HEADER = 'Authorization';
    private const AUTH_PREFIX_BEARER = 'Bearer ';

    public function __construct(
        private DefaultValueService $defaultValues
    ) {}

    public function ensureConfigured(string $providerName): void
    {
        $providerName = $this->defaultValues->normalizeProviderName($providerName);

        Log::info('ProviderRegistry: ensureConfigured called', [
            'provider' => $providerName,
            'context' => 'web_request'
        ]);

        // Special-case: always (re)register 'ollama' to force the OpenAI-compatible implementation
        // This avoids the legacy provider that targets /api/chat with fixed 30s timeout
        if ($providerName === 'ollama') {
            Log::info('ProviderRegistry: registering ollama provider');
            $this->registerOllamaCompat();
            return;
        }

        if (AiBridge::provider($providerName)) {
            Log::info('ProviderRegistry: provider already exists', ['provider' => $providerName]);
            return;
        }

        Log::info('ProviderRegistry: provider not found, attempting registration', ['provider' => $providerName]);

        $map = [
            'openai' => 'registerOpenai',
            'gemini' => 'registerGemini',
            'claude' => 'registerClaude',
            'openrouter' => 'registerOpenrouter',
            'onn' => 'registerOnn',
            'grok' => 'registerGrok',
            'ollama' => 'registerOllamaCompat',
            'ollama_turbo' => 'registerOllamaTurbo',
            'openai_custom' => 'registerOpenaiCustom',
        ];
        if (isset($map[$providerName]) && method_exists($this, $map[$providerName])) {
            $this->{$map[$providerName]}();
            Log::info('ProviderRegistry: provider registered successfully', ['provider' => $providerName]);
        } else {
            Log::warning('ProviderRegistry: no registration method found', ['provider' => $providerName]);
        }
        // Providers like 'ollama' are configured via package config/env; nothing to do otherwise
    }

    /**
     * Register an Ollama provider using its OpenAI-compatible API.
     * Falls back to http://127.0.0.1:11434/v1 if no setting is found.
     */
    private function registerOllamaCompat(): void
    {
        Log::info('ProviderRegistry: registerOllamaCompat called');

        // Utiliser le service de valeurs par défaut pour obtenir l'URL Ollama
        $baseUrl = $this->defaultValues->getEffectiveValue(
            'ollama_base_url',
            null,
            rtrim(config('ollama-laravel.url', env('OLLAMA_URL', 'http://127.0.0.1:11434')), '/')
        );

        Log::info('ProviderRegistry: ollama base URL', ['url' => $baseUrl]);

        // OpenAI compatible endpoint is under /v1
        $base = rtrim($baseUrl, '/') . '/v1';

        Log::info('ProviderRegistry: ollama API base', ['base' => $base]);

        // Standard OpenAI-style endpoints
        $paths = [
            'chat' => '/chat/completions',
            'embeddings' => '/embeddings',
            'image' => '/images/generations',
            'tts' => '/audio/speech',
            'stt' => '/audio/transcriptions',
        ];

        // Local Ollama typically doesn't require auth. However, passing an empty header name
        // can break some HTTP clients. Use a valid header name with an empty key value so no
        // invalid header name is ever sent downstream.
        AiBridge::registerProvider('ollama', new CustomOpenAIProvider(
            '', // API key not required for local Ollama
            $base,
            $paths,
            self::AUTH_HEADER, // use a valid header name (e.g. "Authorization")
            self::AUTH_PREFIX_BEARER, // standard prefix; final value will be empty if key is empty
            [] // extra headers
        ));

        Log::info('ProviderRegistry: ollama provider registered with AiBridge');

        // Verify the provider was registered
        $providerInstance = AiBridge::provider('ollama');
        Log::info('ProviderRegistry: ollama provider verification', [
            'exists' => $providerInstance ? 'YES' : 'NO',
            'class' => $providerInstance ? get_class($providerInstance) : 'N/A'
        ]);
    }

    private function registerOpenai(): void
    {
        $key = $this->defaultValues->getEffectiveValue('openai_api_key', null, '');
        if ($key) { AiBridge::registerProvider('openai', new OpenAIProvider($key)); }
    }

    private function registerGemini(): void
    {
        $key = $this->defaultValues->getEffectiveValue('gemini_api_key', null, '');
        if ($key) { AiBridge::registerProvider('gemini', new GeminiProvider($key)); }
    }

    private function registerClaude(): void
    {
        $key = $this->defaultValues->getEffectiveValue('claude_api_key', null, '');
        if ($key) { AiBridge::registerProvider('claude', new ClaudeProvider($key)); }
    }

    private function registerOpenrouter(): void
    {
        $key = $this->defaultValues->getEffectiveValue('openrouter_api_key', null, '');
        if (!$key) { return; }
        $base = $this->defaultValues->getEffectiveValue('openrouter_base_url', null, 'https://openrouter.ai/api/v1');
        AiBridge::registerProvider('openrouter', new CustomOpenAIProvider(
            $key,
            $base,
            [
                'chat' => '/chat/completions',
                'embeddings' => '/embeddings',
                'image' => '/images/generations',
                'tts' => '/audio/speech',
                'stt' => '/audio/transcriptions',
            ],
            self::AUTH_HEADER,
            self::AUTH_PREFIX_BEARER,
            []
        ));
    }

    private function registerOnn(): void
    {
        $key = $this->defaultValues->getEffectiveValue('onn_api_key', null, '');
        if ($key) { AiBridge::registerProvider('onn', new OnnProvider($key)); }
    }

    private function registerGrok(): void
    {
        $key = $this->defaultValues->getEffectiveValue('grok_api_key', null, '');
        if ($key) { AiBridge::registerProvider('grok', new GrokProvider($key)); }
    }

    private function registerOllamaTurbo(): void
    {
        $key = $this->defaultValues->getEffectiveValue('ollama_turbo_api_key', null, '');
        $endpoint = $this->defaultValues->getEffectiveValue('ollama_turbo_endpoint', null, 'https://ollama.com');
        if ($key) { AiBridge::registerProvider('ollama_turbo', new OllamaTurboProvider($key, $endpoint)); }
    }

    private function registerOpenaiCustom(): void
    {
        $key = $this->defaultValues->getEffectiveValue('openai_custom_api_key', null, '');
        $base = $this->defaultValues->getEffectiveValue('openai_custom_base_url', null, '');
        if (!$key || !$base) { return; }
        $paths = $this->defaultValues->getEffectiveValue('openai_custom_paths', null, [
            'chat' => '/v1/chat/completions',
            'embeddings' => '/v1/embeddings',
            'image' => '/v1/images/generations',
            'tts' => '/v1/audio/speech',
            'stt' => '/v1/audio/transcriptions',
        ]);
        // Ensure header names are valid (non-empty) to avoid HTTP client errors
        $authHeader = trim($this->defaultValues->getEffectiveValue('openai_custom_auth_header', null, self::AUTH_HEADER));
        if ($authHeader === '') { $authHeader = self::AUTH_HEADER; }
        $authPrefix = $this->defaultValues->getEffectiveValue('openai_custom_auth_prefix', null, self::AUTH_PREFIX_BEARER);
        $rawExtra = (array) $this->defaultValues->getEffectiveValue('openai_custom_extra_headers', null, []);
        $extraHeaders = [];
        foreach ($rawExtra as $k => $v) {
            $kn = trim((string) $k);
            if ($kn === '') { continue; }
            $extraHeaders[$kn] = $v;
        }
        AiBridge::registerProvider('openai_custom', new CustomOpenAIProvider($key, $base, $paths, $authHeader, $authPrefix, $extraHeaders));
    }

    public function getSetting(string $key, $default = null)
    {
        $row = DB::table('ai_global_settings')->where('setting_key', $key)->first(['setting_value', 'setting_type', 'is_encrypted']);
        if (!$row) { return $default; }
        $val = $row->setting_value;
        $type = $row->setting_type ?? 'string';
        $encrypted = (bool) ($row->is_encrypted ?? false);
        if ($encrypted && is_string($val) && $val !== '') {
            try { $val = Crypt::decryptString($val); } catch (\Throwable) { /* ignore */ }
        }
        return match ($type) {
            'integer' => (int) $val,
            'boolean' => filter_var($val, FILTER_VALIDATE_BOOLEAN),
            'json' => is_string($val) ? (json_decode($val, true) ?? $default) : $val,
            default => $val,
        };
    }
}
