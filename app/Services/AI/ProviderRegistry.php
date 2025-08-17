<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
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

    public function ensureConfigured(string $providerName): void
    {
        if (AiBridge::provider($providerName)) {
            return;
        }
        $map = [
            'openai' => 'registerOpenai',
            'gemini' => 'registerGemini',
            'claude' => 'registerClaude',
            'openrouter' => 'registerOpenrouter',
            'onn' => 'registerOnn',
            'grok' => 'registerGrok',
            'ollama_turbo' => 'registerOllamaTurbo',
            'openai_custom' => 'registerOpenaiCustom',
        ];
        if (isset($map[$providerName]) && method_exists($this, $map[$providerName])) {
            $this->{$map[$providerName]}();
        }
        // Providers like 'ollama' are configured via package config/env; nothing to do otherwise
    }

    private function registerOpenai(): void
    {
        $key = (string) ($this->getSetting('openai_api_key', '') ?? '');
        if ($key) { AiBridge::registerProvider('openai', new OpenAIProvider($key)); }
    }

    private function registerGemini(): void
    {
        $key = (string) ($this->getSetting('gemini_api_key', '') ?? '');
        if ($key) { AiBridge::registerProvider('gemini', new GeminiProvider($key)); }
    }

    private function registerClaude(): void
    {
        $key = (string) ($this->getSetting('claude_api_key', '') ?? '');
        if ($key) { AiBridge::registerProvider('claude', new ClaudeProvider($key)); }
    }

    private function registerOpenrouter(): void
    {
        $key = (string) ($this->getSetting('openrouter_api_key', '') ?? '');
        if (!$key) { return; }
        $base = (string) ($this->getSetting('openrouter_base_url', 'https://openrouter.ai/api/v1') ?? 'https://openrouter.ai/api/v1');
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
        $key = (string) ($this->getSetting('onn_api_key', '') ?? '');
        if ($key) { AiBridge::registerProvider('onn', new OnnProvider($key)); }
    }

    private function registerGrok(): void
    {
        $key = (string) ($this->getSetting('grok_api_key', '') ?? '');
        if ($key) { AiBridge::registerProvider('grok', new GrokProvider($key)); }
    }

    private function registerOllamaTurbo(): void
    {
        $key = (string) ($this->getSetting('ollama_turbo_api_key', '') ?? '');
        $endpoint = (string) ($this->getSetting('ollama_turbo_endpoint', 'https://ollama.com') ?? 'https://ollama.com');
        if ($key) { AiBridge::registerProvider('ollama_turbo', new OllamaTurboProvider($key, $endpoint)); }
    }

    private function registerOpenaiCustom(): void
    {
        $key = (string) ($this->getSetting('openai_custom_api_key', '') ?? '');
        $base = (string) ($this->getSetting('openai_custom_base_url', '') ?? '');
        if (!$key || !$base) { return; }
        $paths = $this->getSetting('openai_custom_paths', [
            'chat' => '/v1/chat/completions',
            'embeddings' => '/v1/embeddings',
            'image' => '/v1/images/generations',
            'tts' => '/v1/audio/speech',
            'stt' => '/v1/audio/transcriptions',
        ]);
        $authHeader = (string) ($this->getSetting('openai_custom_auth_header', self::AUTH_HEADER) ?? self::AUTH_HEADER);
        $authPrefix = (string) ($this->getSetting('openai_custom_auth_prefix', self::AUTH_PREFIX_BEARER) ?? self::AUTH_PREFIX_BEARER);
        $extraHeaders = (array) ($this->getSetting('openai_custom_extra_headers', []) ?? []);
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
