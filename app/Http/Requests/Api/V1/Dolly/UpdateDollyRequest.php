<?php

namespace App\Http\Requests\Api\V1\Dolly;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'un chariot — D11.
 *
 * Règles reprises de `DollyController::update()` (relu le 2026-08-04), passées en
 * `sometimes` pour la mise à jour partielle (PATCH). `is_public` est toujours
 * réinitialisé à false par le contrôleur.
 */
class UpdateDollyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'category' => 'sometimes|required|in:mail,transaction,record,slip,building,shelf,container,communication,room,digital_folder,digital_document,artifact,book,book_series',
        ];
    }
}
