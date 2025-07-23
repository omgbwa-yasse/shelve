<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SettingValue;
use App\Models\User;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingValueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SettingValue::with(['setting', 'user', 'organisation']);

        // Filtrage par paramètre
        if ($request->has('setting_id') && $request->get('setting_id')) {
            $query->where('setting_id', $request->get('setting_id'));
        }

        // Filtrage par utilisateur
        if ($request->has('user_id') && $request->get('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }

        // Filtrage par organisation
        if ($request->has('organisation_id') && $request->get('organisation_id')) {
            $query->where('organisation_id', $request->get('organisation_id'));
        }

        $values = $query->paginate(15);
        $settings = Setting::with('category')->get();
        $users = User::all();
        $organisations = Organisation::all();

        return view('settings.values.index', compact('values', 'settings', 'users', 'organisations'));
    }

    /**
     * Affiche le formulaire de création d'une valeur
     */
    public function create()
    {
        $settings = Setting::with('category')->get();
        $users = User::all();
        $organisations = Organisation::all();
        return view('settings.values.create', compact('settings', 'users', 'organisations'));
    }

    /**
     * Enregistre une nouvelle valeur
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'setting_id' => 'required|exists:settings,id',
            'user_id' => 'nullable|exists:users,id',
            'organisation_id' => 'nullable|exists:organisations,id',
            'value' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Validation du type de valeur
        $setting = Setting::findOrFail($request->setting_id);
        if (!$this->validateValueForSetting($request->value, $setting)) {
            return redirect()->back()
                ->withErrors(['value' => 'La valeur ne respecte pas le type requis pour ce paramètre.'])
                ->withInput();
        }

        SettingValue::create([
            'setting_id' => $request->setting_id,
            'user_id' => $request->user_id,
            'organisation_id' => $request->organisation_id,
            'value' => json_encode($request->value)
        ]);

        return redirect()->route('settings.values.index')
            ->with('success', 'Valeur créée avec succès.');
    }

    /**
     * Affiche une valeur spécifique
     */
    public function show(SettingValue $settingValue)
    {
        $settingValue->load(['setting.category', 'user', 'organisation']);
        return view('settings.values.show', compact('settingValue'));
    }

    /**
     * Affiche le formulaire d'édition d'une valeur
     */
    public function edit(SettingValue $settingValue)
    {
        $settingValue->load(['setting.category', 'user', 'organisation']);
        $settings = Setting::with('category')->get();
        $users = User::all();
        $organisations = Organisation::all();

        return view('settings.values.edit', compact('settingValue', 'settings', 'users', 'organisations'));
    }

    /**
     * Met à jour une valeur
     */
    public function update(Request $request, SettingValue $settingValue)
    {
        $validator = Validator::make($request->all(), [
            'setting_id' => 'required|exists:settings,id',
            'user_id' => 'nullable|exists:users,id',
            'organisation_id' => 'nullable|exists:organisations,id',
            'value' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Validation du type de valeur
        $setting = Setting::findOrFail($request->setting_id);
        if (!$this->validateValueForSetting($request->value, $setting)) {
            return redirect()->back()
                ->withErrors(['value' => 'La valeur ne respecte pas le type requis pour ce paramètre.'])
                ->withInput();
        }

        $settingValue->update([
            'setting_id' => $request->setting_id,
            'user_id' => $request->user_id,
            'organisation_id' => $request->organisation_id,
            'value' => json_encode($request->value)
        ]);

        return redirect()->route('settings.values.index')
            ->with('success', 'Valeur mise à jour avec succès.');
    }

    /**
     * Supprime une valeur
     */
    public function destroy(SettingValue $settingValue)
    {
        $settingValue->delete();

        return redirect()->route('settings.values.index')
            ->with('success', 'Valeur supprimée avec succès.');
    }

    /**
     * Valide une valeur pour un paramètre donné
     */
    private function validateValueForSetting($value, Setting $setting)
    {
        $type = $setting->type;
        $constraints = is_string($setting->constraints) ? json_decode($setting->constraints, true) : $setting->constraints;

        switch ($type) {
            case 'string':
                if (!is_string($value)) return false;
                if (isset($constraints['max_length']) && strlen($value) > $constraints['max_length']) return false;
                if (isset($constraints['min_length']) && strlen($value) < $constraints['min_length']) return false;
                break;

            case 'integer':
                if (!is_numeric($value) || !is_int((int)$value)) return false;
                if (isset($constraints['min']) && (int)$value < $constraints['min']) return false;
                if (isset($constraints['max']) && (int)$value > $constraints['max']) return false;
                break;

            case 'boolean':
                if (!is_bool($value) && !in_array($value, [0, 1, '0', '1', 'true', 'false'])) return false;
                break;

            case 'json':
                if (is_string($value)) {
                    json_decode($value);
                    if (json_last_error() !== JSON_ERROR_NONE) return false;
                }
                break;

            default:
                return true;
        }

        return true;
    }
}
