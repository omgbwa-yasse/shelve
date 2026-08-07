<?php

namespace App\Http\Requests\Api\V1\Setting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'un paramètre applicatif — D01.
 *
 * `value` n'est pas contraint ici : la validation de type et de contraintes est
 * portée par `SettingValueService` (comme en Blade), pas par le FormRequest.
 */
class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes', 'string', 'max:100',
                Rule::unique('settings', 'name')->ignore($this->route('setting')),
            ],
            'category_id' => 'sometimes|exists:setting_categories,id',
            'type' => 'sometimes|in:integer,string,boolean,json,float,array',
            'default_value' => 'sometimes',
            'description' => 'sometimes|string',
            'is_system' => 'boolean',
            'constraints' => 'nullable|array',
            'user_id' => 'nullable|exists:users,id',
            'organisation_id' => 'nullable|exists:organisations,id',
            'value' => 'nullable',
        ];
    }
}
