<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SettingCategory;
use App\Models\SettingValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function home()
    {
        return view('settings.home', );
    }

    /**
     * Affiche la liste des paramètres
     */
    public function index(Request $request)
    {
        $query = Setting::with(['category', 'values' => function($q) {
            $q->where('user_id', auth()->id())
              ->orWhere('organisation_id', auth()->user()->organisation_id);
        }]);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $settings = $query->get();

        return response()->json($settings);
    }

    /**
     * Affiche un paramètre spécifique
     */
    public function show($id)
    {
        $setting = Setting::with(['category', 'values' => function($q) {
            $q->where('user_id', auth()->id())
              ->orWhere('organisation_id', auth()->user()->organisation_id);
        }])->findOrFail($id);

        return response()->json($setting);
    }

    /**
     * Crée un nouveau paramètre (admin uniquement)
     */
    public function store(Request $request)
    {
        $this->authorize('create', Setting::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'category_id' => 'required|exists:setting_categories,id',
            'type' => 'required|in:integer,string,boolean,json,float,array',
            'default_value' => 'required|json',
            'description' => 'required|string',
            'is_system' => 'boolean',
            'constraints' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $setting = Setting::create($request->all());

        return response()->json($setting, 201);
    }

    /**
     * Met à jour un paramètre (admin uniquement)
     */
    public function update(Request $request, $id)
    {
        $setting = Setting::findOrFail($id);
        $this->authorize('update', $setting);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:100',
            'category_id' => 'exists:setting_categories,id',
            'type' => 'in:integer,string,boolean,json,float,array',
            'default_value' => 'json',
            'description' => 'string',
            'is_system' => 'boolean',
            'constraints' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $setting->update($request->all());

        return response()->json($setting);
    }

    /**
     * Supprime un paramètre (admin uniquement)
     */
    public function destroy($id)
    {
        $setting = Setting::findOrFail($id);
        $this->authorize('delete', $setting);

        $setting->delete();

        return response()->json(null, 204);
    }

    /**
     * Définit une valeur de paramètre pour l'utilisateur actuel
     */
    public function setValue(Request $request, $id)
    {
        $setting = Setting::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'value' => 'required|json',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validation du type de données selon le paramètre
        $value = json_decode($request->value, true);
        if (!$this->validateValueType($value, $setting->type, $setting->constraints)) {
            return response()->json(['errors' => ['value' => 'La valeur ne correspond pas au type de paramètre ou aux contraintes']], 422);
        }

        // Création ou mise à jour de la valeur
        $settingValue = SettingValue::updateOrCreate(
            [
                'setting_id' => $id,
                'user_id' => auth()->id(),
                'organisation_id' => auth()->user()->organisation_id
            ],
            ['value' => $request->value]
        );

        return response()->json($settingValue);
    }

    /**
     * Réinitialise la valeur d'un paramètre à la valeur par défaut
     */
    public function resetValue($id)
    {
        $setting = Setting::findOrFail($id);

        SettingValue::where('setting_id', $id)
            ->where('user_id', auth()->id())
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
