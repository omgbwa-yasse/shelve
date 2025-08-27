<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AI\DefaultValueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AISettingsController extends Controller
{
    public function __construct(
        private DefaultValueService $defaultValues
    ) {}

    /**
     * Récupère le modèle d'IA par défaut depuis la table settings
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDefaultModel()
    {
        try {
            $model = $this->defaultValues->getDefaultModel();

            return response()->json([
                'success' => true,
                'model' => $model
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du modèle par défaut', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération du modèle par défaut'
            ], 500);
        }
    }

    /**
     * Récupère le provider d'IA par défaut
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDefaultProvider()
    {
        try {
            $provider = $this->defaultValues->getDefaultProvider();

            return response()->json([
                'success' => true,
                'provider' => $provider
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du provider par défaut', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération du provider par défaut'
            ], 500);
        }
    }

    /**
     * Récupère tous les paramètres liés à l'IA
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllAISettings()
    {
        try {
            $aiCategory = \App\Models\SettingCategory::where('name', 'Intelligence Artificielle')->first();

            if (!$aiCategory) {
                return response()->json([
                    'success' => false,
                    'error' => 'Catégorie de paramètres IA non trouvée'
                ], 404);
            }

            // Récupérer les paramètres de la catégorie principale et de ses sous-catégories
            $settings = Setting::whereIn('category_id', function ($query) use ($aiCategory) {
                $query->select('id')
                      ->from('setting_categories')
                      ->where('id', $aiCategory->id)
                      ->orWhere('parent_id', $aiCategory->id);
            })->get();

            $formattedSettings = $settings->mapWithKeys(function ($setting) {
                return [$setting->name => $setting->default_value];
            });

            return response()->json([
                'success' => true,
                'settings' => $formattedSettings
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la récupération des paramètres IA: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des paramètres',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
