<?php

namespace Database\Seeders;

use App\Models\SettingCategory;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer/mettre à jour les catégories principales (idempotent)
        $generalCategory = SettingCategory::updateOrCreate(
            ['name' => 'Général'],
            ['description' => 'Paramètres généraux de l\'application', 'parent_id' => null]
        );

        $securityCategory = SettingCategory::updateOrCreate(
            ['name' => 'Sécurité'],
            ['description' => 'Paramètres de sécurité et d\'authentification', 'parent_id' => null]
        );

        $notificationCategory = SettingCategory::updateOrCreate(
            ['name' => 'Notifications'],
            ['description' => 'Paramètres de notifications et d\'alertes', 'parent_id' => null]
        );

        $aiCategory = SettingCategory::updateOrCreate(
            ['name' => 'Intelligence Artificielle'],
            ['description' => 'Paramètres des services d\'IA et des providers', 'parent_id' => null]
        );

        // Sous-catégorie pour les notifications
        $emailNotifCategory = SettingCategory::updateOrCreate(
            ['name' => 'Email'],
            ['description' => 'Paramètres de notifications par email', 'parent_id' => $notificationCategory->id]
        );

        // Sous-catégories pour l'IA
        $aiProvidersCategory = SettingCategory::updateOrCreate(
            ['name' => 'Providers'],
            ['description' => 'Configuration des providers d\'IA', 'parent_id' => $aiCategory->id]
        );

        $aiModelsCategory = SettingCategory::updateOrCreate(
            ['name' => 'Modèles'],
            ['description' => 'Configuration des modèles d\'IA', 'parent_id' => $aiCategory->id]
        );

        // Créer/mettre à jour quelques paramètres d'exemple (idempotent)
        Setting::updateOrCreate(
            ['name' => 'app_name'],
            [
                'category_id' => $generalCategory->id,
                'type' => 'string',
                'default_value' => json_encode('Shelves'),
                'description' => 'Nom de l\'application',
                'is_system' => true,
                'constraints' => json_encode(['max_length' => 100]),
            ]
        );

        Setting::updateOrCreate(
            ['name' => 'maintenance_mode'],
            [
                'category_id' => $generalCategory->id,
                'type' => 'boolean',
                'default_value' => json_encode(false),
                'description' => 'Mode maintenance activé',
                'is_system' => true,
            ]
        );

        Setting::updateOrCreate(
            ['name' => 'session_timeout'],
            [
                'category_id' => $securityCategory->id,
                'type' => 'integer',
                'default_value' => json_encode(3600),
                'description' => 'Délai d\'expiration de session (en secondes)',
                'is_system' => true,
                'constraints' => json_encode(['min' => 300, 'max' => 86400]),
            ]
        );

        Setting::updateOrCreate(
            ['name' => 'password_min_length'],
            [
                'category_id' => $securityCategory->id,
                'type' => 'integer',
                'default_value' => json_encode(8),
                'description' => 'Longueur minimale des mots de passe',
                'is_system' => true,
                'constraints' => json_encode(['min' => 6, 'max' => 50]),
            ]
        );

        Setting::updateOrCreate(
            ['name' => 'email_notifications_enabled'],
            [
                'category_id' => $emailNotifCategory->id,
                'type' => 'boolean',
                'default_value' => json_encode(true),
                'description' => 'Activer les notifications par email',
                'is_system' => false,
            ]
        );

        Setting::updateOrCreate(
            ['name' => 'email_frequency'],
            [
                'category_id' => $emailNotifCategory->id,
                'type' => 'string',
                'default_value' => json_encode('daily'),
                'description' => 'Fréquence des notifications email',
                'is_system' => false,
                'constraints' => json_encode(['options' => ['immediate', 'daily', 'weekly', 'never']]),
            ]
        );

        // Paramètres généraux pour l'IA
        Setting::updateOrCreate(
            ['name' => 'ai_default_provider'],
            [
                'category_id' => $aiCategory->id,
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
            ]
        );

        Setting::updateOrCreate(
            ['name' => 'ai_default_model'],
            [
                'category_id' => $aiCategory->id,
                'type' => 'string',
                'default_value' => json_encode('gemma3:4b'),
                'description' => 'Modèle d\'IA par défaut',
                'is_system' => true,
            ]
        );

        // Limites de taille pour les prompts AI
        Setting::updateOrCreate(
            ['name' => 'ai_max_chars_per_file'],
            [
                'category_id' => $aiCategory->id,
                'type' => 'integer',
                'default_value' => json_encode(200),
                'description' => 'Nombre maximum de caractères à conserver par fichier avant envoi à l\'IA (Drag & Drop).',
                'is_system' => false,
                'constraints' => json_encode(['min' => 200, 'max' => 100000, 'step' => 500]),
            ]
        );

        Setting::updateOrCreate(
            ['name' => 'ai_max_total_chars'],
            [
                'category_id' => $aiCategory->id,
                'type' => 'integer',
                'default_value' => json_encode(40000),
                'description' => 'Budget global maximum de caractères envoyés à l\'IA (après agrégation de tous les fichiers).',
                'is_system' => false,
                'constraints' => json_encode(['min' => 5000, 'max' => 200000, 'step' => 1000]),
            ]
        );

        Setting::updateOrCreate(
            ['name' => 'ai_request_timeout'],
            [
                'category_id' => $aiCategory->id,
                'type' => 'integer',
                'default_value' => json_encode(120),
                'description' => 'Timeout des requêtes IA (en secondes)',
                'is_system' => true,
                'constraints' => json_encode(['min' => 30, 'max' => 300]),
            ]
        );

        // Configuration Ollama
        Setting::updateOrCreate(
            ['name' => 'ollama_base_url'],
            [
                'category_id' => $aiProvidersCategory->id,
                'type' => 'string',
                'default_value' => json_encode('http://localhost:11434'),
                'description' => 'URL de base pour Ollama',
                'is_system' => true,
            ]
        );

        Setting::updateOrCreate(
            ['name' => 'ollama_enabled'],
            [
                'category_id' => $aiProvidersCategory->id,
                'type' => 'boolean',
                'default_value' => json_encode(true),
                'description' => 'Activer le provider Ollama',
                'is_system' => true,
            ]
        );

        // Configuration LM Studio
        Setting::updateOrCreate(
            ['name' => 'lmstudio_base_url'],
            [
                'category_id' => $aiProvidersCategory->id,
                'type' => 'string',
                'default_value' => json_encode('http://localhost:1234'),
                'description' => 'URL de base pour LM Studio',
                'is_system' => true,
            ]
        );

        Setting::updateOrCreate(
            ['name' => 'lmstudio_enabled'],
            [
                'category_id' => $aiProvidersCategory->id,
                'type' => 'boolean',
                'default_value' => json_encode(false),
                'description' => 'Activer le provider LM Studio',
                'is_system' => true,
            ]
        );

        Setting::updateOrCreate(
            ['name' => 'lmstudio_api_key'],
            [
                'category_id' => $aiProvidersCategory->id,
                'type' => 'string',
                'default_value' => json_encode(''),
                'description' => 'Clé API pour LM Studio (optionnelle)',
                'is_system' => true,
            ]
        );

        // Configuration AnythingLLM
        Setting::updateOrCreate(
            ['name' => 'anythingllm_base_url'],
            [
                'category_id' => $aiProvidersCategory->id,
                'type' => 'string',
                'default_value' => json_encode('http://localhost:3001'),
                'description' => 'URL de base pour AnythingLLM',
                'is_system' => true,
            ]
        );

        Setting::updateOrCreate(
            ['name' => 'anythingllm_enabled'],
            [
                'category_id' => $aiProvidersCategory->id,
                'type' => 'boolean',
                'default_value' => json_encode(false),
                'description' => 'Activer le provider AnythingLLM',
                'is_system' => true,
            ]
        );

        Setting::updateOrCreate(
            ['name' => 'anythingllm_api_key'],
            [
                'category_id' => $aiProvidersCategory->id,
                'type' => 'string',
                'default_value' => json_encode(''),
                'description' => 'Clé API pour AnythingLLM',
                'is_system' => true,
            ]
        );

        // Configuration OpenAI
        Setting::updateOrCreate(
            ['name' => 'openai_enabled'],
            [
                'category_id' => $aiProvidersCategory->id,
                'type' => 'boolean',
                'default_value' => json_encode(false),
                'description' => 'Activer le provider OpenAI',
                'is_system' => true,
            ]
        );

        Setting::updateOrCreate(
            ['name' => 'openai_api_key'],
            [
                'category_id' => $aiProvidersCategory->id,
                'type' => 'string',
                'default_value' => json_encode(''),
                'description' => 'Clé API pour OpenAI',
                'is_system' => true,
            ]
        );

        Setting::updateOrCreate(
            ['name' => 'openai_organization'],
            [
                'category_id' => $aiProvidersCategory->id,
                'type' => 'string',
                'default_value' => json_encode(''),
                'description' => 'Organisation OpenAI (optionnelle)',
                'is_system' => true,
            ]
        );

        // Configuration des modèles par défaut
        Setting::updateOrCreate(
            ['name' => 'model_summary'],
            [
                'category_id' => $aiModelsCategory->id,
                'type' => 'string',
                'default_value' => json_encode('gemma3:4b'),
                'description' => 'Modèle pour la génération de résumés',
                'is_system' => true,
            ]
        );

        Setting::updateOrCreate(
            ['name' => 'model_keywords'],
            [
                'category_id' => $aiModelsCategory->id,
                'type' => 'string',
                'default_value' => json_encode('gemma3:4b'),
                'description' => 'Modèle pour l\'extraction de mots-clés',
                'is_system' => true,
            ]
        );

        Setting::updateOrCreate(
            ['name' => 'model_analysis'],
            [
                'category_id' => $aiModelsCategory->id,
                'type' => 'string',
                'default_value' => json_encode('gemma3:4b'),
                'description' => 'Modèle pour l\'analyse de texte',
                'is_system' => true,
            ]
        );
    }
}
