<?php

namespace App\Http\Controllers;

use App\Models\SettingCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingCategoryController extends Controller
{
    /**
     * Affiche la liste des catégories de paramètres
     */
    public function index(Request $request)
    {
        $query = SettingCategory::with(['parent', 'children', 'settings']);

        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->get('parent_id'));
        }

        if ($request->boolean('root_only')) {
            $query->whereNull('parent_id');
        }

        $categories = $query->get();

        return view('settings.categories.index', compact('categories'));
    }

    /**
     * Affiche le formulaire de création d'une catégorie
     */
    public function create()
    {
        $categories = SettingCategory::all();
        return view('settings.categories.create', compact('categories'));
    }

    /**
     * Enregistre une nouvelle catégorie
     */
    public function store(Request $request)
    {
        $this->authorize('create', SettingCategory::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:setting_categories,name',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:setting_categories,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $category = SettingCategory::create($request->all());

        return redirect()->route('settings.categories.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    /**
     * Affiche une catégorie spécifique
     */
    public function show($id)
    {
        $category = SettingCategory::with(['parent', 'children', 'settings'])->findOrFail($id);

        return view('settings.categories.show', compact('category'));
    }

    /**
     * Affiche le formulaire d'édition d'une catégorie
     */
    public function edit($id)
    {
        $category = SettingCategory::findOrFail($id);
        $categories = SettingCategory::where('id', '!=', $id)->get();
        return view('settings.categories.edit', compact('category', 'categories'));
    }

    /**
     * Met à jour une catégorie
     */
    public function update(Request $request, $id)
    {
        $category = SettingCategory::findOrFail($id);
        $this->authorize('update', $category);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:100|unique:setting_categories,name,' . $id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:setting_categories,id|not_in:' . $id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($request->has('parent_id') && $request->parent_id) {
            if ($this->wouldCreateCircularReference($id, $request->parent_id)) {
                return redirect()->back()->withErrors(['parent_id' => 'Cette modification créerait une référence circulaire'])->withInput();
            }
        }

        $category->update($request->all());

        return redirect()->route('settings.categories.index')
            ->with('success', 'Catégorie mise à jour avec succès.');
    }

    /**
     * Supprime une catégorie
     */
    public function destroy($id)
    {
        $category = SettingCategory::findOrFail($id);
        $this->authorize('delete', $category);

        if ($category->settings()->count() > 0) {
            return redirect()->back()->with('error', 'Impossible de supprimer une catégorie qui contient des paramètres.');
        }

        $category->delete();

        return redirect()->route('settings.categories.index')
            ->with('success', 'Catégorie supprimée avec succès.');
    }

    /**
     * Récupère l'arbre hiérarchique complet des catégories
     */
    public function tree()
    {
        $categories = SettingCategory::with(['children.children.children', 'settings'])
            ->whereNull('parent_id')
            ->get();

        return response()->json($categories);
    }

    /**
     * Vérifie si le changement de parent créerait une référence circulaire
     */
    protected function wouldCreateCircularReference($categoryId, $newParentId)
    {
        $category = SettingCategory::find($newParentId);

        while ($category) {
            if ($category->id == $categoryId) {
                return true;
            }
            $category = $category->parent;
        }

        return false;
    }
}
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
