<?php

namespace App\Http\Requests\Api\V1\Dolly;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un chariot — D11.
 *
 * Règles reprises de `DollyController::store()` (relu le 2026-08-04). Le `category`
 * du Blade (`exists:dollies,category`) empêchait la création du premier élément d'une
 * catégorie : remplacé par la liste des valeurs du champ `enum` du schéma.
 * Champs gérés serveur retirés : `is_public`, `created_by`, `owner_organisation_id`.
 */
class StoreDollyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:mail,transaction,record,slip,building,shelf,container,communication,room,digital_folder,digital_document,artifact,book,book_series',
        ];
    }
}
