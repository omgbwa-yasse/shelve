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
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:setting_categories,id',
            'is_system' => 'boolean'
        ]);

        SettingCategory::create($request->all());

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

        if ($request->has('parent_id') && $request->input('parent_id') &&
            $this->wouldCreateCircularReference($id, $request->input('parent_id'))) {
            return redirect()->back()->withErrors(['parent_id' => 'Cette modification créerait une référence circulaire'])->withInput();
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
