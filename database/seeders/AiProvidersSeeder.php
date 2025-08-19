<?php

namespace Database\Seeders;

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
        // Créer ou récupérer la catégorie IA
        $aiCategory = SettingCategory::firstOrCreate(
            ['name' => 'Intelligence Artificielle'],
            ['description' => 'Paramètres des services d\'IA et des providers']
        );

        // Sous-catégories pour l'IA
        $aiProvidersCategory = SettingCategory::firstOrCreate(
            [ 'name' => 'Providers', 'parent_id' => $aiCategory->id ],
            ['description' => 'Configuration des providers d\'IA']
        );

        $aiModelsCategory = SettingCategory::firstOrCreate(
            [ 'name' => 'Modèles', 'parent_id' => $aiCategory->id ],
            ['description' => 'Configuration des modèles d\'IA']
        );

    // Paramètres généraux pour l'IA
    $aiSettings = $this->buildSettings($aiCategory->id, $aiProvidersCategory->id, $aiModelsCategory->id);

        // Upsert pour mettre à jour les valeurs par défaut si déjà existantes
        foreach ($aiSettings as $settingData) {
            Setting::updateOrCreate(
                ['name' => $settingData['name']],
                $settingData
            );
        }

        $this->command->info('Paramètres des providers IA créés avec succès.');
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
                'description' => 'Provider d\'IA par défaut',
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
                'description' => 'Modèle d\'IA par défaut',
                'is_system' => true,
            ],
            [
                'category_id' => $aiCategoryId,
                'name' => 'ai_request_timeout',
                'type' => 'integer',
                'default_value' => json_encode(120),
                'description' => 'Timeout des requêtes IA (en secondes)',
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
                'description' => 'Clé API pour LM Studio (optionnelle)',
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
                'description' => 'Clé API pour AnythingLLM',
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
                'description' => 'Clé API pour OpenAI',
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
            // Configuration des modèles par défaut
            [
                'category_id' => $aiModelsCategoryId,
                'name' => 'model_summary',
                'type' => 'string',
                'default_value' => json_encode(self::DEFAULT_MODEL),
                'description' => 'Modèle pour la génération de résumés',
                'is_system' => true,
            ],
            [
                'category_id' => $aiModelsCategoryId,
                'name' => 'model_keywords',
                'type' => 'string',
                'default_value' => json_encode(self::DEFAULT_MODEL),
                'description' => 'Modèle pour l\'extraction de mots-clés',
                'is_system' => true,
            ],
            [
                'category_id' => $aiModelsCategoryId,
                'name' => 'model_analysis',
                'type' => 'string',
                'default_value' => json_encode(self::DEFAULT_MODEL),
                'description' => 'Modèle pour l\'analyse de texte',
                'is_system' => true,
            ],
        ];
    }
}
