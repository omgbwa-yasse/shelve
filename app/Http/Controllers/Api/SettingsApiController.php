<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur API pour la gestion des paramètres système
 * Utilisé par le serveur MCP pour récupérer la configuration
 */
class SettingsApiController extends Controller
{
    protected $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    /**
     * Récupère un paramètre spécifique
     * @param string $name Nom du paramètre
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSetting($name)
    {
        try {
            $setting = Setting::where('name', $name)
                ->whereNull('user_id')
                ->whereNull('organisation_id')
                ->first();

            if (!$setting) {
                return response()->json([
                    'error' => 'Paramètre non trouvé',
                    'name' => $name
                ], 404);
            }

            $value = $this->settingService->get($name, $setting->default_value);

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

            $result = [];
            foreach ($request->input('settings') as $settingName) {
                $value = $this->settingService->get($settingName);
                $result[$settingName] = $value;
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
            $aiSettings = Setting::whereHas('category', function ($query) {
                $query->where('name', 'Intelligence Artificielle')
                      ->orWhere('parent_id', function ($subQuery) {
                          $subQuery->select('id')
                              ->from('setting_categories')
                              ->where('name', 'Intelligence Artificielle');
                      });
            })
            ->whereNull('user_id')
            ->whereNull('organisation_id')
            ->get();

            $result = [];
            foreach ($aiSettings as $setting) {
                $value = $this->settingService->get($setting->name, $setting->default_value);
                $result[$setting->name] = $value;
            }

            return response()->json([
                'ai_settings' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des paramètres IA',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Met à jour un paramètre
     * @param Request $request
     * @param string $name
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSetting(Request $request, $name)
    {
        try {
            $request->validate([
                'value' => 'required'
            ]);

            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Authentification requise'
                ], 401);
            }

            $success = $this->settingService->set($name, $request->input('value'));

            if (!$success) {
                return response()->json([
                    'error' => 'Paramètre non trouvé ou mise à jour impossible'
                ], 404);
            }

            return response()->json([
                'message' => 'Paramètre mis à jour avec succès',
                'name' => $name,
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
     * Teste la connectivité des providers IA
     * @return \Illuminate\Http\JsonResponse
     */
    public function testAiProviders()
    {
        try {
            $providers = [
                'ollama' => [
                    'name' => 'Ollama',
                    'url' => $this->settingService->get('ollama_base_url', 'http://localhost:11434'),
                    'status' => 'unknown'
                ],
                'lmstudio' => [
                    'name' => 'LM Studio',
                    'url' => $this->settingService->get('lmstudio_base_url', 'http://localhost:1234'),
                    'status' => 'unknown'
                ],
                'anythingllm' => [
                    'name' => 'AnythingLLM',
                    'url' => $this->settingService->get('anythingllm_base_url', 'http://localhost:3001'),
                    'status' => 'unknown'
                ]
            ];

            foreach ($providers as &$provider) {
                $provider['status'] = $this->testProvider($provider['url']);
            }

            return response()->json([
                'providers' => $providers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du test des providers',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Teste la connectivité d'un provider
     * @param string $url
     * @return string
     */
    private function testProvider($url)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url . '/api/tags');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return ($httpCode >= 200 && $httpCode < 300) ? 'online' : 'offline';
        } catch (\Exception $e) {
            return 'offline';
        }
    }
}
