<?php
// Script de configuration de la clé API Google Speech-to-Text

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Setting;

echo "🎤 CONFIGURATION GOOGLE SPEECH-TO-TEXT API\n";
echo "==========================================\n\n";

// REMPLACEZ CETTE VALEUR PAR VOTRE VRAIE CLÉ API
$apiKey = 'AIzaSyA2u6FYFvFghzTP-ZAeJKdqxb1m2Y72nf0';

if ($apiKey === 'VOTRE_CLE_API_GOOGLE_SPEECH_ICI') {
    echo "❌ ERREUR: Veuillez remplacer la clé API dans ce fichier\n";
    echo "   Ligne 16: \$apiKey = 'VOTRE_VRAIE_CLE_API';\n\n";

    echo "🔗 Pour obtenir une clé API:\n";
    echo "   1. Allez sur https://console.cloud.google.com/\n";
    echo "   2. Activez l'API 'Cloud Speech-to-Text'\n";
    echo "   3. Créez une clé API dans 'Credentials'\n";
    echo "   4. Copiez la clé et remplacez-la dans ce fichier\n\n";
    exit(1);
}

try {
    echo "📝 Configuration de Google Speech-to-Text...\n\n";

    // 1. Clé API Google Speech
    $apiKeySetting = Setting::where('name', 'google_speech_api_key')->first();
    if ($apiKeySetting) {
        $apiKeySetting->update(['value' => json_encode($apiKey)]);
        echo "✅ Clé API Google Speech configurée\n";
    } else {
        echo "❌ Paramètre google_speech_api_key non trouvé\n";
    }

    // 2. Activation Google Speech
    $enabledSetting = Setting::where('name', 'google_speech_enabled')->first();
    if ($enabledSetting) {
        $enabledSetting->update(['value' => json_encode(true)]);
        echo "✅ Google Speech activé\n";
    } else {
        echo "❌ Paramètre google_speech_enabled non trouvé\n";
    }

    echo "\n🎉 GOOGLE SPEECH-TO-TEXT CONFIGURÉ !\n";
    echo "\n📋 INFORMATIONS:\n";
    echo "   • API: Google Cloud Speech-to-Text\n";
    echo "   • Status: Activé ✅\n";
    echo "   • Fallback: Web Speech API (navigateur)\n";

    echo "\n🚀 UTILISATION:\n";
    echo "   • Interface: http://127.0.0.1:8000/ai-search\n";
    echo "   • Cliquez sur l'icône microphone 🎤\n";
    echo "   • Parlez en français\n\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
?>