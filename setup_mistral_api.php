<?php
// Script de configuration de la clé API Mistral

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Setting;
use Illuminate\Support\Facades\DB;

echo "🔑 CONFIGURATION CLEF API MISTRAL\n";
echo "=================================\n\n";

$apiKey = '';

try {
    // Créer ou mettre à jour les paramètres Mistral
    echo "📝 Création/mise à jour des paramètres Mistral...\n\n";

    // 1. Clé API Mistral
    $apiKeySetting = Setting::where('name', 'mistral_api_key')->first();
    if (!$apiKeySetting) {
        Setting::create([
            'category_id' => 6, // Providers
            'name' => 'mistral_api_key',
            'type' => 'string',
            'default_value' => json_encode(''),
            'description' => 'Clé API pour Mistral AI',
            'is_system' => true,
        ]);
        echo "✅ Paramètre mistral_api_key créé\n";
    }

    $apiKeySetting = Setting::where('name', 'mistral_api_key')->first();
    $apiKeySetting->update(['value' => json_encode($apiKey)]);
    echo "✅ Clé API Mistral configurée\n";

    // 2. Activation Mistral
    $enabledSetting = Setting::where('name', 'mistral_enabled')->first();
    if (!$enabledSetting) {
        Setting::create([
            'category_id' => 4, // Intelligence Artificielle
            'name' => 'mistral_enabled',
            'type' => 'boolean',
            'default_value' => json_encode(false),
            'description' => 'Activer le provider Mistral AI',
            'is_system' => true,
        ]);
        echo "✅ Paramètre mistral_enabled créé\n";
    }

    $enabledSetting = Setting::where('name', 'mistral_enabled')->first();
    $enabledSetting->update(['value' => json_encode(true)]);
    echo "✅ Provider Mistral activé\n";

    // 3. URL de base Mistral
    $baseUrlSetting = Setting::where('name', 'mistral_base_url')->first();
    if (!$baseUrlSetting) {
        Setting::create([
            'category_id' => 6, // Providers
            'name' => 'mistral_base_url',
            'type' => 'string',
            'default_value' => json_encode('https://api.mistral.ai/v1'),
            'description' => 'URL de base pour l\'API Mistral',
            'is_system' => true,
        ]);
        echo "✅ Paramètre mistral_base_url créé\n";
    }

    // 4. Modèle par défaut Mistral
    $defaultModelSetting = Setting::where('name', 'mistral_default_model')->first();
    if (!$defaultModelSetting) {
        Setting::create([
            'category_id' => 7, // Modèles
            'name' => 'mistral_default_model',
            'type' => 'string',
            'default_value' => json_encode('mistral-large-latest'),
            'description' => 'Modèle Mistral par défaut à utiliser',
            'is_system' => true,
        ]);
        echo "✅ Paramètre mistral_default_model créé\n";
    }

    // 5. Mettre à jour le provider par défaut du système vers Mistral
    $defaultProviderSetting = Setting::where('name', 'ai_default_provider')->first();
    if ($defaultProviderSetting) {
        // Ajouter mistral aux options si pas déjà présent
        $constraints = json_decode($defaultProviderSetting->constraints, true) ?? [];
        if (isset($constraints['options']) && !in_array('mistral', $constraints['options'])) {
            $constraints['options'][] = 'mistral';
            $defaultProviderSetting->update(['constraints' => json_encode($constraints)]);
            echo "✅ Mistral ajouté aux options de providers\n";
        }

        // Changer le provider par défaut vers Mistral
        $defaultProviderSetting->update(['value' => json_encode('mistral')]);
        echo "✅ Provider par défaut changé vers Mistral\n";
    }

    // 6. Mettre à jour le modèle par défaut du système vers Mistral
    $defaultModelSystemSetting = Setting::where('name', 'ai_default_model')->first();
    if ($defaultModelSystemSetting) {
        $defaultModelSystemSetting->update(['value' => json_encode('mistral-large-latest')]);
        echo "✅ Modèle par défaut changé vers mistral-large-latest\n";
    }

    // 7. Désactiver Ollama pour éviter les conflits
    $ollamaEnabledSetting = Setting::where('name', 'ollama_enabled')->first();
    if ($ollamaEnabledSetting) {
        $ollamaEnabledSetting->update(['value' => json_encode(false)]);
        echo "✅ Provider Ollama désactivé\n";
    }

    // 8. Mettre à jour les modèles spécifiques vers Mistral
    $specificModels = ['model_summary', 'model_keywords', 'model_analysis'];
    foreach ($specificModels as $modelName) {
        $modelSetting = Setting::where('name', $modelName)->first();
        if ($modelSetting) {
            $modelSetting->update(['value' => json_encode('mistral-large-latest')]);
            echo "✅ {$modelName} mis à jour vers mistral-large-latest\n";
        }
    }

    // Vérifier la configuration
    echo "\n🔍 VÉRIFICATION DE LA CONFIGURATION:\n";

    $settings = Setting::whereIn('name', [
        'mistral_api_key',
        'mistral_enabled',
        'mistral_base_url',
        'mistral_default_model',
        'ai_default_provider',
        'ai_default_model',
        'ollama_enabled',
        'model_summary',
        'model_keywords',
        'model_analysis'
    ])->get();

    foreach ($settings as $setting) {
        $value = $setting->value !== null ? json_decode($setting->value, true) : json_decode($setting->default_value, true);
        
        if ($setting->name === 'mistral_api_key') {
            $displayValue = '***' . substr($value, -4); // Masquer la clé sauf les 4 derniers caractères
        } else {
            $displayValue = is_bool($value) ? ($value ? 'true' : 'false') : $value;
        }
        
        echo "   {$setting->name}: {$displayValue}\n";
    }

    echo "\n🎉 MISTRAL AI EST MAINTENANT CONFIGURÉ ET ACTIF !\n";
    echo "\n📋 INFORMATIONS:\n";
    echo "   • Provider: mistral\n";
    echo "   • Modèle: mistral-large-latest\n";
    echo "   • API URL: https://api.mistral.ai/v1\n";
    echo "   • Status: Activé ✅\n";
    
    echo "\n🚀 VOUS POUVEZ MAINTENANT TESTER:\n";
    echo "   • Interface: http://127.0.0.1:8000/ai-search\n";
    echo "   • Test: \"combien d'auteurs dans le système\"\n\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
?>