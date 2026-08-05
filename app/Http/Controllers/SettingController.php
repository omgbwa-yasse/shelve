<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SettingCategory;
use App\Models\User;
use App\Models\Organisation;
use App\Services\Settings\SettingValueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function __construct(private SettingValueService $settingValue)
    {
    }

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
        $users = \App\Models\User::orderBy('name')->get();
        $organisations = \App\Models\Organisation::orderBy('name')->get();

        return view('settings.definitions.create', compact('categories', 'users', 'organisations'));
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
        $setting = Setting::with(['category', 'user', 'organisation'])->findOrFail($id);
        $categories = SettingCategory::all();
        $users = \App\Models\User::orderBy('name')->get();
        $organisations = \App\Models\Organisation::orderBy('name')->get();

        return view('settings.definitions.edit', compact('setting', 'categories', 'users', 'organisations'));
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
            'user_id' => 'nullable|exists:users,id',
            'organisation_id' => 'nullable|exists:organisations,id',
            'value' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $setting = Setting::create($request->all());

        return redirect()->route('settings.definitions.index')
            ->with('success', 'Paramètre créé avec succès.');
    }

    /**
     * Met à jour la valeur personnalisée d'un paramètre
     */
    public function update(Request $request, $id)
    {
        $setting = Setting::findOrFail($id);
        $this->authorize('update', $setting);

        // Validation pour la valeur seulement
        $validator = Validator::make($request->all(), [
            'value' => 'nullable', // La validation sera faite par convertValueToType
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Convertir et valider la valeur selon le type du paramètre
        $value = $request->input('value');

        // Si la valeur est vide ou null, on supprime la valeur personnalisée
        if (empty($value) && $value !== false && $value !== 0 && $value !== '0') {
            $setting->value = null;
        } else {
            // Convertir la valeur selon le type
            $convertedValue = $this->settingValue->convertValueToType($value, $setting->type);

            // Valider la valeur convertie
            if (!$this->settingValue->validateValueType($convertedValue, $setting->type, $setting->constraints ?? [])) {
                return redirect()->back()
                    ->withErrors(['value' => 'La valeur fournie ne respecte pas les contraintes du paramètre.'])
                    ->withInput();
            }

            $setting->value = $convertedValue;
        }

        $setting->save();

        return redirect()->route('settings.definitions.show', $setting)
            ->with('success', 'Votre valeur personnalisée a été sauvegardée avec succès.');
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

        // Récupération et conversion de la valeur selon le type
        $rawValue = $request->input('value');
        $value = $this->settingValue->convertValueToType($rawValue, $setting->type);

        // Validation du type de données selon le paramètre
        if (!$this->settingValue->validateValueType($value, $setting->type, $setting->constraints)) {
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

}
