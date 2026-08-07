<?php

namespace App\Http\Requests\Api\V1\MailTypology;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une typologie de courrier — D06.
 *
 * Règles reprises de `MailTypologyController::store()` (relu le 2026-08-04),
 * complétées par `code`, exigé par le schéma (colonne NOT NULL sans défaut).
 */
class StoreMailTypologyRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|max:5',
            'name' => 'required|unique:mail_typologies|max:50',
            'description' => 'nullable|max:100',
            'activity_id' => 'required|exists:activities,id',
        ];
    }
}
