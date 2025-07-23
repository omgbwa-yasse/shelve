<?php

namespace Database\Seeders;

use App\Models\SettingCategory;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class AiProvidersSeeder extends Seeder
{
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
            [
                'name' => 'Providers',
                'parent_id' => $aiCategory->id
            ],
            ['description' => 'Configuration des providers d\'IA']
        );

        $aiModelsCategory = SettingCategory::firstOrCreate(
            [
                'name' => 'Modèles',
                'parent_id' => $aiCategory->id
            ],
            ['description' => 'Configuration des modèles d\'IA']
        );

        // Paramètres généraux pour l'IA
        $aiSettings = [
            [
                'category_id' => $aiCategory->id,
                'name' => 'ai_default_provider',
                'type' => 'string',
                'default_value' => json_encode('ollama'),
                'description' => 'Provider d\'IA par défaut',
                'is_system' => true,
                'constraints' => json_encode(['options' => ['ollama', 'lmstudio', 'anythingllm', 'openai']]),
            ],
            [
                'category_id' => $aiCategory->id,
                'name' => 'ai_default_model',
                'type' => 'string',
                'default_value' => json_encode('llama3'),
                'description' => 'Modèle d\'IA par défaut',
                'is_system' => true,
            ],
            [
                'category_id' => $aiCategory->id,
                'name' => 'ai_request_timeout',
                'type' => 'integer',
                'default_value' => json_encode(120),
                'description' => 'Timeout des requêtes IA (en secondes)',
                'is_system' => true,
                'constraints' => json_encode(['min' => 30, 'max' => 300]),
            ],
            // Configuration Ollama
            [
                'category_id' => $aiProvidersCategory->id,
                'name' => 'ollama_base_url',
                'type' => 'string',
                'default_value' => json_encode('http://localhost:11434'),
                'description' => 'URL de base pour Ollama',
                'is_system' => true,
            ],
            [
                'category_id' => $aiProvidersCategory->id,
                'name' => 'ollama_enabled',
                'type' => 'boolean',
                'default_value' => json_encode(true),
                'description' => 'Activer le provider Ollama',
                'is_system' => true,
            ],
            // Configuration LM Studio
            [
                'category_id' => $aiProvidersCategory->id,
                'name' => 'lmstudio_base_url',
                'type' => 'string',
                'default_value' => json_encode('http://localhost:1234'),
                'description' => 'URL de base pour LM Studio',
                'is_system' => true,
            ],
            [
                'category_id' => $aiProvidersCategory->id,
                'name' => 'lmstudio_enabled',
                'type' => 'boolean',
                'default_value' => json_encode(false),
                'description' => 'Activer le provider LM Studio',
                'is_system' => true,
            ],
            [
                'category_id' => $aiProvidersCategory->id,
                'name' => 'lmstudio_api_key',
                'type' => 'string',
                'default_value' => json_encode(''),
                'description' => 'Clé API pour LM Studio (optionnelle)',
                'is_system' => true,
            ],
            // Configuration AnythingLLM
            [
                'category_id' => $aiProvidersCategory->id,
                'name' => 'anythingllm_base_url',
                'type' => 'string',
                'default_value' => json_encode('http://localhost:3001'),
                'description' => 'URL de base pour AnythingLLM',
                'is_system' => true,
            ],
            [
                'category_id' => $aiProvidersCategory->id,
                'name' => 'anythingllm_enabled',
                'type' => 'boolean',
                'default_value' => json_encode(false),
                'description' => 'Activer le provider AnythingLLM',
                'is_system' => true,
            ],
            [
                'category_id' => $aiProvidersCategory->id,
                'name' => 'anythingllm_api_key',
                'type' => 'string',
                'default_value' => json_encode(''),
                'description' => 'Clé API pour AnythingLLM',
                'is_system' => true,
            ],
            // Configuration OpenAI
            [
                'category_id' => $aiProvidersCategory->id,
                'name' => 'openai_enabled',
                'type' => 'boolean',
                'default_value' => json_encode(false),
                'description' => 'Activer le provider OpenAI',
                'is_system' => true,
            ],
            [
                'category_id' => $aiProvidersCategory->id,
                'name' => 'openai_api_key',
                'type' => 'string',
                'default_value' => json_encode(''),
                'description' => 'Clé API pour OpenAI',
                'is_system' => true,
            ],
            [
                'category_id' => $aiProvidersCategory->id,
                'name' => 'openai_organization',
                'type' => 'string',
                'default_value' => json_encode(''),
                'description' => 'Organisation OpenAI (optionnelle)',
                'is_system' => true,
            ],
            // Configuration des modèles par défaut
            [
                'category_id' => $aiModelsCategory->id,
                'name' => 'model_summary',
                'type' => 'string',
                'default_value' => json_encode('llama3'),
                'description' => 'Modèle pour la génération de résumés',
                'is_system' => true,
            ],
            [
                'category_id' => $aiModelsCategory->id,
                'name' => 'model_keywords',
                'type' => 'string',
                'default_value' => json_encode('llama3'),
                'description' => 'Modèle pour l\'extraction de mots-clés',
                'is_system' => true,
            ],
            [
                'category_id' => $aiModelsCategory->id,
                'name' => 'model_analysis',
                'type' => 'string',
                'default_value' => json_encode('llama3'),
                'description' => 'Modèle pour l\'analyse de texte',
                'is_system' => true,
            ],
        ];

        // Créer les paramètres s'ils n'existent pas déjà
        foreach ($aiSettings as $settingData) {
            Setting::firstOrCreate(
                ['name' => $settingData['name']],
                $settingData
            );
        }

        $this->command->info('Paramètres des providers IA créés avec succès.');
    }
}
