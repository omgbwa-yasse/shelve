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
            'user_id' => 'nullable|exists:users,id',
            'organisation_id' => 'nullable|exists:organisations,id',
            'value' => 'nullable|json',
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

        // Récupération et conversion de la valeur selon le type
        $rawValue = $request->input('value');
        $value = $this->convertValueToType($rawValue, $setting->type);

        // Validation du type de données selon le paramètre
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
     * Convertit une valeur string vers le type approprié
     */
    protected function convertValueToType($value, $type)
    {
        switch ($type) {
            case 'integer':
                return (int) $value;

            case 'float':
                return (float) $value;

            case 'boolean':
                // Accepte différents formats pour les booleans
                if (is_string($value)) {
                    $lowerValue = strtolower(trim($value));
                    if (in_array($lowerValue, ['true', '1', 'yes', 'on'])) {
                        return true;
                    } elseif (in_array($lowerValue, ['false', '0', 'no', 'off', ''])) {
                        return false;
                    }
                }
                return (bool) $value;

            case 'array':
            case 'json':
                // Essaie de décoder le JSON, sinon garde la valeur originale
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    if ($decoded !== null) {
                        return $decoded;
                    }
                }
                return $value;

            case 'string':
            default:
                return (string) $value;
        }
    }

    /**
     * Valide le type de la valeur en fonction du type attendu et des contraintes
     */
    protected function validateValueType($value, $type, $constraints = null)
    {
        $constraints = json_decode($constraints, true) ?? [];

        $methodName = 'validate' . ucfirst($type) . 'Type';
        if (method_exists($this, $methodName)) {
            return $this->{$methodName}($value, $constraints);
        }

        return false;
    }

    /**
     * Valide une valeur de type integer
     */
    protected function validateIntegerType($value, array $constraints = [])
    {
        $isValid = true;

        if (!is_int($value)) {
            $isValid = false;
        } elseif (isset($constraints['min']) && $value < $constraints['min']) {
            $isValid = false;
        } elseif (isset($constraints['max']) && $value > $constraints['max']) {
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * Valide une valeur de type float
     */
    protected function validateFloatType($value, array $constraints = [])
    {
        $isValid = true;

        if (!is_numeric($value)) {
            $isValid = false;
        } elseif (isset($constraints['min']) && $value < $constraints['min']) {
            $isValid = false;
        } elseif (isset($constraints['max']) && $value > $constraints['max']) {
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * Valide une valeur de type string
     */
    protected function validateStringType($value, array $constraints = [])
    {
        $isValid = true;

        if (!is_string($value)) {
            $isValid = false;
        } elseif (isset($constraints['min_length']) && strlen($value) < $constraints['min_length']) {
            $isValid = false;
        } elseif (isset($constraints['max_length']) && strlen($value) > $constraints['max_length']) {
            $isValid = false;
        } elseif (isset($constraints['pattern']) && !preg_match($constraints['pattern'], $value)) {
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * Valide une valeur de type boolean
     */
    protected function validateBooleanType($value, array $constraints = [])
    {
        return is_bool($value);
    }

    /**
     * Valide une valeur de type array
     */
    protected function validateArrayType($value, array $constraints = [])
    {
        $isValid = true;

        if (!is_array($value)) {
            $isValid = false;
        } elseif (isset($constraints['min_items']) && count($value) < $constraints['min_items']) {
            $isValid = false;
        } elseif (isset($constraints['max_items']) && count($value) > $constraints['max_items']) {
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * Valide une valeur de type json
     */
    protected function validateJsonType($value, array $constraints = [])
    {
        // Pour le JSON, on accepte tout car il sera re-validé lors du décodage
        return true;
    }
}
