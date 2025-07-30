<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AISettingsController extends Controller
{
    /**
     * Récupère le modèle d'IA par défaut depuis la table settings
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDefaultModel()
    {
        try {
            $defaultModel = Setting::where('name', 'ai_default_model')->first();

            // Vérifie si la colonne default_value existe avant d'accéder à sa valeur
            if ($defaultModel && !array_key_exists('default_value', $defaultModel->getAttributes())) {
                return response()->json([
                    'success' => false,
                    'error' => "La colonne 'default_value' n'existe pas dans la table settings"
                ], 500);
            }

            if ($defaultModel) {
                return response()->json([
                    'success' => true,
                    'model' => $defaultModel->default_value
                ]);
            }

            // Modèle par défaut si le paramètre n'est pas trouvé en base
            return response()->json([
                'success' => true,
                'model' => 'gemma3:4b',
                'note' => 'Valeur par défaut utilisée, paramètre non trouvé en base de données'
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la récupération du modèle d'IA par défaut: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération du modèle par défaut',
                'details' => $e->getMessage()
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
