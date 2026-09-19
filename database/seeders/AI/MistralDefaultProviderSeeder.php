<?php

namespace Database\Seeders\AI;

use App\Models\Setting;
use App\Models\SettingCategory;
use Illuminate\Database\Seeder;

/**
 * Configure Mistral comme provider IA par défaut, à partir de config/mistral.php
 * (valeurs AI_MISTRAL_* du .env). Aucune clé n'est écrite en dur dans le code.
 *
 *   php artisan config:clear
 *   php artisan db:seed --class="Database\Seeders\AI\MistralDefaultProviderSeeder"
 *
 * Écrit dans la colonne `value` (jamais `default_value`) : c'est le
 * mécanisme de surcharge lu en priorité par Setting::getEffectiveValue(),
 * donc ce réglage survit à un futur reseed de AiProvidersSeeder (qui ne pose
 * que des default_value via firstOrCreate et ne l'écrasera jamais).
 */
class MistralDefaultProviderSeeder extends Seeder
{
    public function run(): void
    {
        $apiKey = trim((string) config('mistral.api_key'));
        $model = trim((string) config('mistral.model')) ?: 'mistral-small-latest';
        $baseUrl = trim((string) config('mistral.base_url')) ?: 'https://api.mistral.ai/v1';

        if ($apiKey === '') {
            $this->command->warn('AI_MISTRAL_API_KEY est vide dans le .env : Mistral non configuré.');
            $this->command->warn('Renseignez-la, puis : php artisan config:clear');

            return;
        }

        $category = SettingCategory::firstOrCreate(
            ['name' => 'Intelligence Artificielle'],
            ['description' => 'Paramètres des services d\'IA et des providers']
        );

        $set = function (string $name, $value, string $description) use ($category) {
            $setting = Setting::firstOrCreate(
                ['name' => $name],
                [
                    'category_id' => $category->id,
                    'type' => 'string',
                    'default_value' => json_encode(''),
                    'description' => $description,
                    'is_system' => true,
                ]
            );

            $setting->value = json_encode($value);
            $setting->save();
        };

        $set('ai_default_provider', 'mistral', 'Provider d\'IA par défaut');
        $set('mistral_api_key', $apiKey, 'Clé API Mistral');
        $set('mistral_default_model', $model, 'Modèle Mistral par défaut');
        $set('mistral_base_url', $baseUrl, 'URL de base API Mistral');
        // ai_default_model est lu tel quel par certains écrans (drag-drop des records,
        // application IA). On l'aligne sur un modèle Mistral valide, sinon l'API Mistral
        // reçoit un modèle Ollama (ex. gemma3:4b) inconnu → « AI Agent Stream Error ».
        $set('ai_default_model', $model, 'Modèle IA par défaut (aligné sur le provider actif)');

        $this->command->info(sprintf(
            'Mistral configuré : modèle %s, clé %s…%s',
            $model,
            substr($apiKey, 0, 4),
            substr($apiKey, -2)
        ));
    }
}
