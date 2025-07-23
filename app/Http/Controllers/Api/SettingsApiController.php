<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SettingValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur API pour la gestion des paramètres système
 * Utilisé par le serveur MCP pour récupérer la configuration
 */
class SettingsApiController extends Controller
{
    /**
     * Récupère un paramètre spécifique
     * @param string $name Nom du paramètre
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSetting($name)
    {
        try {
            $setting = Setting::where('name', $name)->first();

            if (!$setting) {
                return response()->json([
                    'error' => 'Paramètre non trouvé',
                    'name' => $name
                ], 404);
            }

            $value = $this->getSettingValue($setting);

            return response()->json([
                'name' => $setting->name,
                'value' => $value,
                'type' => $setting->type,
                'description' => $setting->description
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération du paramètre',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère plusieurs paramètres en une seule requête
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSettings(Request $request)
    {
        try {
            $request->validate([
                'settings' => 'required|array',
                'settings.*' => 'required|string'
            ]);

            $settingNames = $request->input('settings');
            $settings = Setting::whereIn('name', $settingNames)->get();

            $result = [];
            foreach ($settings as $setting) {
                $value = $this->getSettingValue($setting);
                $result[$setting->name] = $value;
            }

            // Ajouter les paramètres manquants avec leurs valeurs par défaut
            foreach ($settingNames as $name) {
                if (!isset($result[$name])) {
                    $result[$name] = null;
                }
            }

            return response()->json([
                'settings' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des paramètres',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère tous les paramètres liés à l'IA
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAiSettings()
    {
        try {
            $settings = Setting::whereHas('category', function ($query) {
                $query->where('name', 'Intelligence Artificielle')
                      ->orWhere('parent_id', function ($subQuery) {
                          $subQuery->select('id')
                                   ->from('setting_categories')
                                   ->where('name', 'Intelligence Artificielle');
                      });
            })->get();

            $result = [];
            foreach ($settings as $setting) {
                $value = $this->getSettingValue($setting);
                $result[$setting->name] = [
                    'value' => $value,
                    'type' => $setting->type,
                    'description' => $setting->description,
                    'category' => $setting->category->name
                ];
            }

            return response()->json([
                'settings' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des paramètres IA',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Met à jour un paramètre (pour les paramètres non système uniquement)
     * @param Request $request
     * @param string $name
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSetting(Request $request, $name)
    {
        try {
            $setting = Setting::where('name', $name)->first();

            if (!$setting) {
                return response()->json([
                    'error' => 'Paramètre non trouvé'
                ], 404);
            }

            if ($setting->is_system) {
                return response()->json([
                    'error' => 'Les paramètres système ne peuvent pas être modifiés via l\'API'
                ], 403);
            }

            $request->validate([
                'value' => 'required'
            ]);

            $user = Auth::user();
            $organisation = $user->organisation;

            // Chercher ou créer la valeur personnalisée
            $settingValue = SettingValue::firstOrCreate([
                'setting_id' => $setting->id,
                'user_id' => $user->id,
                'organisation_id' => $organisation->id ?? null
            ]);

            $settingValue->value = json_encode($request->input('value'));
            $settingValue->save();

            return response()->json([
                'message' => 'Paramètre mis à jour avec succès',
                'name' => $setting->name,
                'value' => $request->input('value')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la mise à jour du paramètre',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère la valeur effective d'un paramètre
     * (valeur personnalisée utilisateur/organisation ou valeur par défaut)
     * @param Setting $setting
     * @return mixed
     */
    private function getSettingValue(Setting $setting)
    {
        $user = Auth::user();

        if ($user) {
            // Chercher une valeur personnalisée pour l'utilisateur/organisation
            $customValue = SettingValue::where('setting_id', $setting->id)
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                          ->orWhere('organisation_id', $user->organisation_id ?? null);
                })
                ->orderBy('user_id', 'desc') // Priorité à la valeur utilisateur
                ->first();

            if ($customValue) {
                return json_decode($customValue->value, true);
            }
        }

        // Utiliser la valeur par défaut
        return json_decode($setting->default_value, true);
    }

    /**
     * Teste la connectivité avec les providers d'IA configurés
     * @return \Illuminate\Http\JsonResponse
     */
    public function testAiProviders()
    {
        try {
            $aiSettings = $this->getAiSettingsArray();
            $results = [];

            // Test Ollama
            if ($aiSettings['ollama_enabled'] ?? false) {
                $results['ollama'] = $this->testProvider('ollama', $aiSettings['ollama_base_url'] ?? 'http://localhost:11434');
            }

            // Test LM Studio
            if ($aiSettings['lmstudio_enabled'] ?? false) {
                $results['lmstudio'] = $this->testProvider('lmstudio', $aiSettings['lmstudio_base_url'] ?? 'http://localhost:1234');
            }

            // Test AnythingLLM
            if ($aiSettings['anythingllm_enabled'] ?? false) {
                $results['anythingllm'] = $this->testProvider('anythingllm', $aiSettings['anythingllm_base_url'] ?? 'http://localhost:3001');
            }

            // Test OpenAI
            if ($aiSettings['openai_enabled'] ?? false) {
                $results['openai'] = $this->testProvider('openai', 'https://api.openai.com/v1', $aiSettings['openai_api_key'] ?? '');
            }

            return response()->json([
                'providers' => $results,
                'default_provider' => $aiSettings['ai_default_provider'] ?? 'ollama'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du test des providers',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère tous les paramètres IA sous forme de tableau
     * @return array
     */
    private function getAiSettingsArray()
    {
        $settings = Setting::whereHas('category', function ($query) {
            $query->where('name', 'Intelligence Artificielle')
                  ->orWhere('parent_id', function ($subQuery) {
                      $subQuery->select('id')
                               ->from('setting_categories')
                               ->where('name', 'Intelligence Artificielle');
                  });
        })->get();

        $result = [];
        foreach ($settings as $setting) {
            $value = $this->getSettingValue($setting);
            $result[$setting->name] = $value;
        }

        return $result;
    }

    /**
     * Teste la connectivité avec un provider spécifique
     * @param string $name
     * @param string $baseUrl
     * @param string $apiKey
     * @return array
     */
    private function testProvider($name, $baseUrl, $apiKey = '')
    {
        try {
            $client = new \GuzzleHttp\Client(['timeout' => 5]);
            $headers = ['Accept' => 'application/json'];

            if ($apiKey) {
                $headers['Authorization'] = "Bearer {$apiKey}";
            }

            if ($name === 'ollama') {
                $url = $baseUrl . '/api/tags';
            } else {
                $url = $baseUrl . '/v1/models';
            }

            $response = $client->get($url, ['headers' => $headers]);

            return [
                'status' => 'connected',
                'response_code' => $response->getStatusCode(),
                'url' => $url
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
                'url' => $url ?? $baseUrl
            ];
        }
    }
}
