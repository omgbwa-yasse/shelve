<?php

namespace Database\Seeders\AI;

use App\Models\SettingCategory;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class AiProvidersSeeder extends Seeder
{
    private const DEFAULT_MODEL = 'gemma3:4b';
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // CrÃ©er ou rÃ©cupÃ©rer la catÃ©gorie IA
        $aiCategory = SettingCategory::firstOrCreate(
            ['name' => 'Intelligence Artificielle'],
            ['description' => 'ParamÃ¨tres des services d\'IA et des providers']
        );

        // Sous-catÃ©gories pour l'IA
        $aiProvidersCategory = SettingCategory::firstOrCreate(
            [ 'name' => 'Providers', 'parent_id' => $aiCategory->id ],
            ['description' => 'Configuration des providers d\'IA']
        );

        $aiModelsCategory = SettingCategory::firstOrCreate(
            [ 'name' => 'ModÃ¨les', 'parent_id' => $aiCategory->id ],
            ['description' => 'Configuration des modÃ¨les d\'IA']
        );

    // ParamÃ¨tres gÃ©nÃ©raux pour l'IA
    $aiSettings = $this->buildSettings($aiCategory->id, $aiProvidersCategory->id, $aiModelsCategory->id);

        // Upsert pour mettre Ã  jour les valeurs par dÃ©faut si dÃ©jÃ  existantes
        foreach ($aiSettings as $settingData) {
            Setting::updateOrCreate(
                ['name' => $settingData['name']],
                $settingData
            );
        }

        $this->command->info('ParamÃ¨tres des providers IA crÃ©Ã©s avec succÃ¨s.');
    }

    private function buildSettings(int $aiCategoryId, int $aiProvidersCategoryId, int $aiModelsCategoryId): array
    {
        return array_merge(
            $this->generalSettings($aiCategoryId),
            $this->providerSettings($aiProvidersCategoryId),
            $this->modelSettings($aiModelsCategoryId)
        );
    }

    private function generalSettings(int $aiCategoryId): array
    {
        return [
            [
                'category_id' => $aiCategoryId,
                'name' => 'ai_default_provider',
                'type' => 'string',
                'default_value' => json_encode('ollama'),
                'description' => 'Provider d\'IA par dÃ©faut',
                'is_system' => true,
                'constraints' => json_encode(['options' => [
                    'ollama',
                    'openai',
                    'gemini',
                    'claude',
                    'openrouter',
                    'onn',
                    'ollama_turbo',
                    'openai_custom',
                ]]),
            ],
            [
                'category_id' => $aiCategoryId,
                'name' => 'ai_default_model',
                'type' => 'string',
                'default_value' => json_encode(self::DEFAULT_MODEL),
                'description' => 'ModÃ¨le d\'IA par dÃ©faut',
                'is_system' => true,
            ],
            [
                'category_id' => $aiCategoryId,
                'name' => 'ai_request_timeout',
                'type' => 'integer',
                'default_value' => json_encode(120),
                'description' => 'Timeout des requÃªtes IA (en secondes)',
                'is_system' => true,
                'constraints' => json_encode(['min' => 30, 'max' => 300]),
            ],
        ];
    }

    private function providerSettings(int $aiProvidersCategoryId): array
    {
        return [
            // Configuration Ollama
            [
                'category_id' => $aiProvidersCategoryId,
                'name' => 'ollama_base_url',
                'type' => 'string',
                'default_value' => json_encode('http://localhost:11434'),
                'description' => 'URL de base pour Ollama',
                'is_system' => true,
            ],
            [
                'category_id' => $aiProvidersCategoryId,
                'name' => 'ollama_enabled',
                'type' => 'boolean',
                'default_value' => json_encode(true),
                'description' => 'Activer le provider Ollama',
                'is_system' => true,
            ],
            // Configuration LM Studio
            [
                'category_id' => $aiProvidersCategoryId,
                'name' => 'lmstudio_base_url',
                'type' => 'string',
                'default_value' => json_encode('http://localhost:1234'),
                'description' => 'URL de base pour LM Studio',
                'is_system' => true,
            ],
            [
                'category_id' => $aiProvidersCategoryId,
                'name' => 'lmstudio_enabled',
                'type' => 'boolean',
                'default_value' => json_encode(false),
                'description' => 'Activer le provider LM Studio',
                'is_system' => true,
            ],
            [
                'category_id' => $aiProvidersCategoryId,
                'name' => 'lmstudio_api_key',
                'type' => 'string',
                'default_value' => json_encode(''),
                'description' => 'ClÃ© API pour LM Studio (optionnelle)',
                'is_system' => true,
            ],
            // Configuration AnythingLLM
            [
                'category_id' => $aiProvidersCategoryId,
                'name' => 'anythingllm_base_url',
                'type' => 'string',
                'default_value' => json_encode('http://localhost:3001'),
                'description' => 'URL de base pour AnythingLLM',
                'is_system' => true,
            ],
            [
                'category_id' => $aiProvidersCategoryId,
                'name' => 'anythingllm_enabled',
                'type' => 'boolean',
                'default_value' => json_encode(false),
                'description' => 'Activer le provider AnythingLLM',
                'is_system' => true,
            ],
            [
                'category_id' => $aiProvidersCategoryId,
                'name' => 'anythingllm_api_key',
                'type' => 'string',
                'default_value' => json_encode(''),
                'description' => 'ClÃ© API pour AnythingLLM',
                'is_system' => true,
            ],
            // Configuration OpenAI
            [
                'category_id' => $aiProvidersCategoryId,
                'name' => 'openai_enabled',
                'type' => 'boolean',
                'default_value' => json_encode(false),
                'description' => 'Activer le provider OpenAI',
                'is_system' => true,
            ],
            [
                'category_id' => $aiProvidersCategoryId,
                'name' => 'openai_api_key',
                'type' => 'string',
                'default_value' => json_encode(''),
                'description' => 'ClÃ© API pour OpenAI',
                'is_system' => true,
            ],
            [
                'category_id' => $aiProvidersCategoryId,
                'name' => 'openai_organization',
                'type' => 'string',
                'default_value' => json_encode(''),
                'description' => 'Organisation OpenAI (optionnelle)',
                'is_system' => true,
            ],
        ];
    }

    private function modelSettings(int $aiModelsCategoryId): array
    {
        return [
            // Configuration des modÃ¨les par dÃ©faut
            [
                'category_id' => $aiModelsCategoryId,
                'name' => 'model_summary',
                'type' => 'string',
                'default_value' => json_encode(self::DEFAULT_MODEL),
                'description' => 'ModÃ¨le pour la gÃ©nÃ©ration de rÃ©sumÃ©s',
                'is_system' => true,
            ],
            [
                'category_id' => $aiModelsCategoryId,
                'name' => 'model_keywords',
                'type' => 'string',
                'default_value' => json_encode(self::DEFAULT_MODEL),
                'description' => 'ModÃ¨le pour l\'extraction de mots-clÃ©s',
                'is_system' => true,
            ],
            [
                'category_id' => $aiModelsCategoryId,
                'name' => 'model_analysis',
                'type' => 'string',
                'default_value' => json_encode(self::DEFAULT_MODEL),
                'description' => 'ModÃ¨le pour l\'analyse de texte',
                'is_system' => true,
            ],
        ];
    }
}

