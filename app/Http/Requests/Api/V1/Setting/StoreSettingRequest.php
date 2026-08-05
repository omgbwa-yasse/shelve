<?php

namespace App\Http\Requests\Api\V1\Setting;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un paramètre applicatif — D01.
 *
 * Règles reprises de `SettingController::store()` (Blade, relu le 2026-08-04).
 * `default_value` et `constraints` sont des JSON ; le modèle les caste en tableau.
 */
class StoreSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:settings,name',
            'category_id' => 'required|exists:setting_categories,id',
            'type' => 'required|in:integer,string,boolean,json,float,array',
            'default_value' => 'required',
            'description' => 'required|string',
            'is_system' => 'boolean',
            'constraints' => 'nullable|array',
            'user_id' => 'nullable|exists:users,id',
            'organisation_id' => 'nullable|exists:organisations,id',
            'value' => 'nullable',
        ];
    }
}
