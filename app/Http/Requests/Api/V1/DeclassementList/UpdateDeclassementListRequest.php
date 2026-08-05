<?php

namespace App\Http\Requests\Api\V1\DeclassementList;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'une liste de déclassement — D07.
 *
 * Comme en Blade, seuls `name` et `description` sont modifiables (le `code` est
 * immuable après création). Le contrôleur refuse la modification d'une liste déjà
 * soumise pour approbation (422).
 */
class UpdateDeclassementListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|max:200',
            'description' => 'nullable',
        ];
    }
}
