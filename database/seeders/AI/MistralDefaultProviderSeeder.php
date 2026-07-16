<?php

namespace Database\Seeders\AI;

use App\Models\Setting;
use App\Models\SettingCategory;
use Illuminate\Database\Seeder;

/**
 * Configure Mistral comme provider IA par défaut, avec sa clé API.
 *
 * Écrit dans la colonne `value` (jamais `default_value`) : c'est le
 * mécanisme de surcharge lu en priorité par Setting::getEffectiveValue(),
 * donc ce réglage survit à un futur reseed de AiProvidersSeeder (qui ne pose
 * que des default_value via firstOrCreate et ne l'écrasera jamais).
 *
 * À exécuter après AiProvidersSeeder (crée ai_default_provider) ; les
 * settings mistral_* n'existent nulle part ailleurs, ce seeder les crée.
 */
class MistralDefaultProviderSeeder extends Seeder
{
    private const API_KEY = '4Ck3BnQOXSLJb0SpahFmqUt7mjHm8xsV';

    public function run(): void
    {
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
        $set('mistral_api_key', self::API_KEY, 'Clé API Mistral');
        $set('mistral_default_model', 'mistral-large-latest', 'Modèle Mistral par défaut');
        $set('mistral_base_url', 'https://api.mistral.ai/v1', 'URL de base API Mistral');

        $this->command->info('Mistral configuré comme provider IA par défaut.');
    }
}
