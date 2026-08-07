<?php

namespace App\Http\Requests\Api\V1\Law;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une loi — D01.
 *
 * `LawController` (Blade) était vide et n'exposait aucune vue : ce module n'avait
 * jamais été branché. Ces règles sont donc entièrement dérivées du schéma, sans
 * comportement Blade à reprendre — relu et complété le 2026-08-04 (ajout des
 * contraintes `exists` sur `law_type_id`, absentes du fallback généré).
 */
class StoreLawRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:10'],
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'publish_date' => ['required', 'date'],
            'law_type_id' => ['required', 'integer', 'exists:law_types,id'],
        ];
    }
}
