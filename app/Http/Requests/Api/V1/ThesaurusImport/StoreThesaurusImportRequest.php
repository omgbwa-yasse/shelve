<?php

namespace App\Http\Requests\Api\V1\ThesaurusImport;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Import de thésaurus — D08.
 *
 * Règles reprises du flux `ThesaurusController::importFile()` / `Api\ThesaurusImportController`
 * (relues le 2026-08-05) : un fichier SKOS-RDF/CSV/JSON + un mode de fusion
 * (replace / merge / append), avec schéma cible optionnel.
 */
class StoreThesaurusImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|max:20480',
            'format' => 'required|in:skos-rdf,csv,json',
            'scheme_id' => 'nullable|exists:thesaurus_schemes,id',
            'language' => 'nullable|string|max:32',
            'merge_mode' => ['required', Rule::in(['replace', 'merge', 'append'])],
        ];
    }
}
