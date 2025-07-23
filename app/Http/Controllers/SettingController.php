<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SettingCategory;
use App\Models\User;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{

    /**
     * Affiche la liste des paramètres
     */
    public function index(Request $request)
    {
        $query = Setting::with(['category', 'user', 'organisation']);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        if ($request->has('is_system')) {
            $query->where('is_system', $request->boolean('is_system'));
        }

        // Filtre pour afficher les paramètres de l'utilisateur actuel
        if (Auth::check()) {
            $userId = Auth::id();
            $organisationId = Auth::user()->organisation_active_id ?? null;

            $query->forUserAndOrganisation($userId, $organisationId);
        }

        $settings = $query->get();

        return view('settings.definitions.index', compact('settings'));
    }

    /**
     * Affiche le formulaire de création d'un paramètre
     */
    public function create()
    {
        $categories = SettingCategory::all();
        return view('settings.definitions.create', compact('categories'));
    }

    /**
     * Affiche un paramètre spécifique
     */
    public function show($id)
    {
        $setting = Setting::with(['category', 'user', 'organisation'])->findOrFail($id);

        return view('settings.definitions.show', compact('setting'));
    }

    /**
     * Affiche le formulaire d'édition d'un paramètre
     */
    public function edit($id)
    {
        $setting = Setting::findOrFail($id);
        $categories = SettingCategory::all();
        return view('settings.definitions.edit', compact('setting', 'categories'));
    }

    /**
     * Crée un nouveau paramètre (admin uniquement)
     */
    public function store(Request $request)
    {
        $this->authorize('create', Setting::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:settings,name',
            'category_id' => 'required|exists:setting_categories,id',
            'type' => 'required|in:integer,string,boolean,json,float,array',
            'default_value' => 'required|json',
            'description' => 'required|string',
            'is_system' => 'boolean',
            'constraints' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $setting = Setting::create($request->all());

        return redirect()->route('settings.definitions.index')
            ->with('success', 'Paramètre créé avec succès.');
    }

    /**
     * Met à jour un paramètre (admin uniquement)
     */
    public function update(Request $request, $id)
    {
        $setting = Setting::findOrFail($id);
        $this->authorize('update', $setting);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:100|unique:settings,name,' . $id,
            'category_id' => 'exists:setting_categories,id',
            'type' => 'in:integer,string,boolean,json,float,array',
            'default_value' => 'json',
            'description' => 'string',
            'is_system' => 'boolean',
            'constraints' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $setting->update($request->all());

        return redirect()->route('settings.definitions.index')
            ->with('success', 'Paramètre mis à jour avec succès.');
    }

    /**
     * Supprime un paramètre (admin uniquement)
     */
    public function destroy($id)
    {
        $setting = Setting::findOrFail($id);
        $this->authorize('delete', $setting);

        $setting->delete();

        return redirect()->route('settings.definitions.index')
            ->with('success', 'Paramètre supprimé avec succès.');
    }

    /**
     * Définit une valeur de paramètre pour l'utilisateur actuel
     */
    public function setValue(Request $request, $id)
    {
        $setting = Setting::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'value' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validation du type de données selon le paramètre
        $value = $request->input('value');
        if (!$this->validateValueType($value, $setting->type, $setting->constraints)) {
            return response()->json(['errors' => ['value' => 'La valeur ne correspond pas au type de paramètre ou aux contraintes']], 422);
        }

        // Crée une nouvelle instance du paramètre avec une valeur personnalisée
        $personalizedSetting = Setting::create([
            'category_id' => $setting->category_id,
            'name' => $setting->name,
            'type' => $setting->type,
            'default_value' => $setting->default_value,
            'description' => $setting->description,
            'is_system' => $setting->is_system,
            'constraints' => $setting->constraints,
            'user_id' => Auth::id(),
            'organisation_id' => Auth::user()->organisation_active_id ?? null,
            'value' => $value
        ]);

        return response()->json($personalizedSetting);
    }

    /**
     * Réinitialise la valeur d'un paramètre à la valeur par défaut
     */
    public function resetValue($id)
    {
        $setting = Setting::findOrFail($id);

        // Supprime les paramètres personnalisés de l'utilisateur
        Setting::where('name', $setting->name)
            ->where('user_id', Auth::id())
            ->where('organisation_id', Auth::user()->organisation_active_id ?? null)
            ->delete();

        return response()->json(['message' => 'Paramètre réinitialisé avec succès']);
    }

    /**
     * Valide le type de la valeur en fonction du type attendu et des contraintes
     */
    protected function validateValueType($value, $type, $constraints = null)
    {
        $constraints = json_decode($constraints, true) ?? [];

        switch ($type) {
            case 'integer':
                if (!is_int($value)) return false;
                if (isset($constraints['min']) && $value < $constraints['min']) return false;
                if (isset($constraints['max']) && $value > $constraints['max']) return false;
                break;

            case 'float':
                if (!is_numeric($value)) return false;
                if (isset($constraints['min']) && $value < $constraints['min']) return false;
                if (isset($constraints['max']) && $value > $constraints['max']) return false;
                break;

            case 'string':
                if (!is_string($value)) return false;
                if (isset($constraints['min_length']) && strlen($value) < $constraints['min_length']) return false;
                if (isset($constraints['max_length']) && strlen($value) > $constraints['max_length']) return false;
                if (isset($constraints['pattern']) && !preg_match($constraints['pattern'], $value)) return false;
                break;

            case 'boolean':
                if (!is_bool($value)) return false;
                break;

            case 'array':
                if (!is_array($value)) return false;
                if (isset($constraints['min_items']) && count($value) < $constraints['min_items']) return false;
                if (isset($constraints['max_items']) && count($value) > $constraints['max_items']) return false;
                break;

            case 'json':
                // Déjà validé par le décodage JSON
                break;
        }

        return true;
    }
}
