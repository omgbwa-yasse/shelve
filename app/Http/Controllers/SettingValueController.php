<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SettingValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingValueController extends Controller
{
    /**
     * Liste des valeurs de paramètres pour l'utilisateur actuel
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = SettingValue::with('setting')
            ->where(function($q) {
                $q->where('user_id', auth()->id())
                  ->orWhere('organisation_id', auth()->user()->organisation_id);
            });

        if ($request->has('setting_id')) {
            $query->where('setting_id', $request->setting_id);
        }

        $values = $query->get();

        return response()->json([
            'success' => true,
            'data' => $values,
            'count' => $values->count()
        ]);
    }

    /**
     * Affiche une valeur de paramètre spécifique
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $value = SettingValue::with('setting')
            ->where(function($q) {
                $q->where('user_id', auth()->id())
                  ->orWhere('organisation_id', auth()->user()->organisation_id);
            })
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $value
        ]);
    }

    /**
     * Crée ou met à jour une valeur de paramètre
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'setting_id' => 'required|exists:settings,id',
            'value' => 'required|json',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $setting = Setting::findOrFail($request->setting_id);

        // Validation du type de données selon le paramètre
        $value = json_decode($request->value, true);
        $constraints = json_decode($setting->constraints, true) ?? [];

        if (!$this->validateValueType($value, $setting->type, $constraints)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'value' => 'La valeur ne correspond pas au type de paramètre ou aux contraintes'
                ]
            ], 422);
        }

        // Création ou mise à jour de la valeur
        $settingValue = SettingValue::updateOrCreate(
            [
                'setting_id' => $request->setting_id,
                'user_id' => auth()->id(),
                'organisation_id' => auth()->user()->organisation_id
            ],
            ['value' => $request->value]
        );

        return response()->json([
            'success' => true,
            'data' => $settingValue,
            'message' => 'Paramètre enregistré avec succès'
        ], 201);
    }

    /**
     * Met à jour une valeur de paramètre existante
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $settingValue = SettingValue::where(function($q) {
                $q->where('user_id', auth()->id())
                  ->orWhere('organisation_id', auth()->user()->organisation_id);
            })
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'value' => 'required|json',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $setting = Setting::findOrFail($settingValue->setting_id);

        // Validation du type de données selon le paramètre
        $value = json_decode($request->value, true);
        $constraints = json_decode($setting->constraints, true) ?? [];

        if (!$this->validateValueType($value, $setting->type, $constraints)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'value' => 'La valeur ne correspond pas au type de paramètre ou aux contraintes'
                ]
            ], 422);
        }

        $settingValue->update([
            'value' => $request->value
        ]);

        return response()->json([
            'success' => true,
            'data' => $settingValue,
            'message' => 'Paramètre mis à jour avec succès'
        ]);
    }

    /**
     * Réinitialise une valeur de paramètre (supprime la personnalisation)
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $settingValue = SettingValue::where(function($q) {
                $q->where('user_id', auth()->id())
                  ->orWhere('organisation_id', auth()->user()->organisation_id);
            })
            ->findOrFail($id);

        $settingValue->delete();

        return response()->json([
            'success' => true,
            'message' => 'Paramètre réinitialisé avec succès'
        ]);
    }

    /**
     * Valide le type de la valeur en fonction du type attendu et des contraintes
     *
     * @param mixed $value Valeur à valider
     * @param string $type Type attendu
     * @param array $constraints Contraintes à vérifier
     * @return bool
     */
    protected function validateValueType($value, $type, $constraints = [])
    {
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

    /**
     * Récupère toutes les valeurs par défaut et personnalisées pour l'utilisateur actuel
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllValues(Request $request)
    {
        $userId = auth()->id();
        $orgId = auth()->user()->organisation_id;

        $query = Setting::with(['category']);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $settings = $query->get();

        $result = $settings->map(function($setting) use ($userId, $orgId) {
            $value = SettingValue::where('setting_id', $setting->id)
                ->where(function($q) use ($userId, $orgId) {
                    $q->where('user_id', $userId);
                    if ($orgId) {
                        $q->orWhere('organisation_id', $orgId);
                    }
                })
                ->orderByRaw('CASE WHEN user_id IS NOT NULL THEN 0 ELSE 1 END')
                ->first();

            $actualValue = $value ? json_decode($value->value, true) : json_decode($setting->default_value, true);

            return [
                'id' => $setting->id,
                'name' => $setting->name,
                'category' => $setting->category->name,
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
            'data' => $result,
            'count' => $result->count()
        ]);
    }
}
