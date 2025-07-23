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
        // Créer les catégories principales
        $generalCategory = SettingCategory::create([
            'name' => 'Général',
            'description' => 'Paramètres généraux de l\'application',
        ]);

        $securityCategory = SettingCategory::create([
            'name' => 'Sécurité',
            'description' => 'Paramètres de sécurité et d\'authentification',
        ]);

        $notificationCategory = SettingCategory::create([
            'name' => 'Notifications',
            'description' => 'Paramètres de notifications et d\'alertes',
        ]);

        $aiCategory = SettingCategory::create([
            'name' => 'Intelligence Artificielle',
            'description' => 'Paramètres des services d\'IA et des providers',
        ]);

        // Sous-catégorie pour les notifications
        $emailNotifCategory = SettingCategory::create([
            'name' => 'Email',
            'description' => 'Paramètres de notifications par email',
            'parent_id' => $notificationCategory->id,
        ]);

        // Sous-catégories pour l'IA
        $aiProvidersCategory = SettingCategory::create([
            'name' => 'Providers',
            'description' => 'Configuration des providers d\'IA',
            'parent_id' => $aiCategory->id,
        ]);

        $aiModelsCategory = SettingCategory::create([
            'name' => 'Modèles',
            'description' => 'Configuration des modèles d\'IA',
            'parent_id' => $aiCategory->id,
        ]);

        // Créer quelques paramètres d'exemple
        Setting::create([
            'category_id' => $generalCategory->id,
            'name' => 'app_name',
            'type' => 'string',
            'default_value' => json_encode('Shelves'),
            'description' => 'Nom de l\'application',
            'is_system' => true,
            'constraints' => json_encode(['max_length' => 100]),
        ]);

        Setting::create([
            'category_id' => $generalCategory->id,
            'name' => 'maintenance_mode',
            'type' => 'boolean',
            'default_value' => json_encode(false),
            'description' => 'Mode maintenance activé',
            'is_system' => true,
        ]);

        Setting::create([
            'category_id' => $securityCategory->id,
            'name' => 'session_timeout',
            'type' => 'integer',
            'default_value' => json_encode(3600),
            'description' => 'Délai d\'expiration de session (en secondes)',
            'is_system' => true,
            'constraints' => json_encode(['min' => 300, 'max' => 86400]),
        ]);

        Setting::create([
            'category_id' => $securityCategory->id,
            'name' => 'password_min_length',
            'type' => 'integer',
            'default_value' => json_encode(8),
            'description' => 'Longueur minimale des mots de passe',
            'is_system' => true,
            'constraints' => json_encode(['min' => 6, 'max' => 50]),
        ]);

        Setting::create([
            'category_id' => $emailNotifCategory->id,
            'name' => 'email_notifications_enabled',
            'type' => 'boolean',
            'default_value' => json_encode(true),
            'description' => 'Activer les notifications par email',
            'is_system' => false,
        ]);

        Setting::create([
            'category_id' => $emailNotifCategory->id,
            'name' => 'email_frequency',
            'type' => 'string',
            'default_value' => json_encode('daily'),
            'description' => 'Fréquence des notifications email',
            'is_system' => false,
            'constraints' => json_encode(['options' => ['immediate', 'daily', 'weekly', 'never']]),
        ]);

        // Paramètres généraux pour l'IA
        Setting::create([
            'category_id' => $aiCategory->id,
            'name' => 'ai_default_provider',
            'type' => 'string',
            'default_value' => json_encode('ollama'),
            'description' => 'Provider d\'IA par défaut',
            'is_system' => true,
            'constraints' => json_encode(['options' => ['ollama', 'lmstudio', 'anythingllm', 'openai']]),
        ]);

        Setting::create([
            'category_id' => $aiCategory->id,
            'name' => 'ai_default_model',
            'type' => 'string',
            'default_value' => json_encode('llama3'),
            'description' => 'Modèle d\'IA par défaut',
            'is_system' => true,
        ]);

        Setting::create([
            'category_id' => $aiCategory->id,
            'name' => 'ai_request_timeout',
            'type' => 'integer',
            'default_value' => json_encode(120),
            'description' => 'Timeout des requêtes IA (en secondes)',
            'is_system' => true,
            'constraints' => json_encode(['min' => 30, 'max' => 300]),
        ]);

        // Configuration Ollama
        Setting::create([
            'category_id' => $aiProvidersCategory->id,
            'name' => 'ollama_base_url',
            'type' => 'string',
            'default_value' => json_encode('http://localhost:11434'),
            'description' => 'URL de base pour Ollama',
            'is_system' => true,
        ]);

        Setting::create([
            'category_id' => $aiProvidersCategory->id,
            'name' => 'ollama_enabled',
            'type' => 'boolean',
            'default_value' => json_encode(true),
            'description' => 'Activer le provider Ollama',
            'is_system' => true,
        ]);

        // Configuration LM Studio
        Setting::create([
            'category_id' => $aiProvidersCategory->id,
            'name' => 'lmstudio_base_url',
            'type' => 'string',
            'default_value' => json_encode('http://localhost:1234'),
            'description' => 'URL de base pour LM Studio',
            'is_system' => true,
        ]);

        Setting::create([
            'category_id' => $aiProvidersCategory->id,
            'name' => 'lmstudio_enabled',
            'type' => 'boolean',
            'default_value' => json_encode(false),
            'description' => 'Activer le provider LM Studio',
            'is_system' => true,
        ]);

        Setting::create([
            'category_id' => $aiProvidersCategory->id,
            'name' => 'lmstudio_api_key',
            'type' => 'string',
            'default_value' => json_encode(''),
            'description' => 'Clé API pour LM Studio (optionnelle)',
            'is_system' => true,
        ]);

        // Configuration AnythingLLM
        Setting::create([
            'category_id' => $aiProvidersCategory->id,
            'name' => 'anythingllm_base_url',
            'type' => 'string',
            'default_value' => json_encode('http://localhost:3001'),
            'description' => 'URL de base pour AnythingLLM',
            'is_system' => true,
        ]);

        Setting::create([
            'category_id' => $aiProvidersCategory->id,
            'name' => 'anythingllm_enabled',
            'type' => 'boolean',
            'default_value' => json_encode(false),
            'description' => 'Activer le provider AnythingLLM',
            'is_system' => true,
        ]);

        Setting::create([
            'category_id' => $aiProvidersCategory->id,
            'name' => 'anythingllm_api_key',
            'type' => 'string',
            'default_value' => json_encode(''),
            'description' => 'Clé API pour AnythingLLM',
            'is_system' => true,
        ]);

        // Configuration OpenAI
        Setting::create([
            'category_id' => $aiProvidersCategory->id,
            'name' => 'openai_enabled',
            'type' => 'boolean',
            'default_value' => json_encode(false),
            'description' => 'Activer le provider OpenAI',
            'is_system' => true,
        ]);

        Setting::create([
            'category_id' => $aiProvidersCategory->id,
            'name' => 'openai_api_key',
            'type' => 'string',
            'default_value' => json_encode(''),
            'description' => 'Clé API pour OpenAI',
            'is_system' => true,
        ]);

        Setting::create([
            'category_id' => $aiProvidersCategory->id,
            'name' => 'openai_organization',
            'type' => 'string',
            'default_value' => json_encode(''),
            'description' => 'Organisation OpenAI (optionnelle)',
            'is_system' => true,
        ]);

        // Configuration des modèles par défaut
        Setting::create([
            'category_id' => $aiModelsCategory->id,
            'name' => 'model_summary',
            'type' => 'string',
            'default_value' => json_encode('llama3'),
            'description' => 'Modèle pour la génération de résumés',
            'is_system' => true,
        ]);

        Setting::create([
            'category_id' => $aiModelsCategory->id,
            'name' => 'model_keywords',
            'type' => 'string',
            'default_value' => json_encode('llama3'),
            'description' => 'Modèle pour l\'extraction de mots-clés',
            'is_system' => true,
        ]);

        Setting::create([
            'category_id' => $aiModelsCategory->id,
            'name' => 'model_analysis',
            'type' => 'string',
            'default_value' => json_encode('llama3'),
            'description' => 'Modèle pour l\'analyse de texte',
            'is_system' => true,
        ]);
    }
}
