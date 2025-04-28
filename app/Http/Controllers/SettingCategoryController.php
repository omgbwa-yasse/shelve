<?php

namespace App\Http\Controllers;

use App\Models\SettingCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingCategoryController extends Controller
{
    /**
     * Affiche la liste des catégories de paramètres
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $categories = SettingCategory::all();

        return response()->json([
            'success' => true,
            'data' => $categories,
            'count' => $categories->count()
        ]);
    }

    /**
     * Enregistre une nouvelle catégorie
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:setting_categories',
            'description' => 'nullable|string',
            'is_system' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $category = SettingCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_system' => $request->is_system ?? false
        ]);

        return response()->json([
            'success' => true,
            'data' => $category,
            'message' => 'Catégorie créée avec succès'
        ], 201);
    }

    /**
     * Affiche une catégorie spécifique
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $category = SettingCategory::with('settings')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    /**
     * Met à jour une catégorie
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $category = SettingCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:100|unique:setting_categories,name,' . $id,
            'description' => 'nullable|string',
            'is_system' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Ne pas permettre la modification des catégories système sauf par un admin
        if ($category->is_system && !auth()->user()->can('manage-system-settings')) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à modifier une catégorie système'
            ], 403);
        }

        $category->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $category,
            'message' => 'Catégorie mise à jour avec succès'
        ]);
    }

    /**
     * Supprime une catégorie
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $category = SettingCategory::findOrFail($id);

        // Ne pas permettre la suppression des catégories système
        if ($category->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer une catégorie système'
            ], 403);
        }

        // Vérifier si la catégorie contient des paramètres
        if ($category->settings()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer une catégorie contenant des paramètres'
            ], 409);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Catégorie supprimée avec succès'
        ]);
    }

    /**
     * Récupère les paramètres d'une catégorie avec leurs valeurs pour l'utilisateur actuel
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSettings($id)
    {
        $category = SettingCategory::findOrFail($id);
        $userId = auth()->id();
        $orgId = auth()->user()->organisation_id ?? null;

        $settings = $category->getSettingsWithValues($userId, $orgId);

        $formattedSettings = $settings->map(function($setting) {
            $value = $setting->values->first();
            $actualValue = $value ? json_decode($value->value, true) : json_decode($setting->default_value, true);

            return [
                'id' => $setting->id,
                'name' => $setting->name,
                'type' => $setting->type,
                'value' => $actualValue,
                'description' => $setting->description,
                'is_system' => $setting->is_system,
                'is_default' => $value ? false : true,
                'constraints' => json_decode($setting->constraints, true),
                'setting_value_id' => $value ? $value->id : null
            ];
        });

        return response()->json([
            'success' => true,
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description
            ],
            'settings' => $formattedSettings,
            'count' => $formattedSettings->count()
        ]);
    }
}
