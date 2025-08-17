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
        $this->seedSettings($now);
    }

    private function seedPrompts($now): void
    {
        // Seed Prompts (system prompts used as base instructions)
        $prompts = [
            [
                'title' => 'record_reformulate',
                'content' => "Tu es un assistant archivistique. Reformule les titres de dossiers pour qu'ils soient clairs, concis et informatifs. Respecte la langue d'origine (FR). Ne renvoie que le nouveau titre sans commentaire.",
                'is_system' => true,
            ],
            [
                'title' => 'record_summarize',
                'content' => "Tu es un assistant archivistique. Résume des contenus de dossiers en 3 à 5 phrases maximum, en français, en conservant les informations clés et le contexte administratif.",
                'is_system' => true,
            ],
            [
                'title' => 'assign_activity',
                'content' => "Tu aides à l'indexation par activités. Choisis les activités les plus pertinentes parmi une liste fournie, en te basant uniquement sur le contenu.",
                'is_system' => true,
            ],
            [
                'title' => 'assign_thesaurus',
                'content' => "Tu aides à l'indexation avec un thésaurus. Propose des libellés préférentiels pertinents à partir d'une liste fournie.",
                'is_system' => true,
            ],
            [
                'title' => 'slip_summarize',
                'content' => "Tu es un assistant archivistique. Génère un résumé synthétique d'un ensemble de slips (bordereaux), en mettant en évidence les points saillants.",
                'is_system' => true,
            ],
        ];

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
