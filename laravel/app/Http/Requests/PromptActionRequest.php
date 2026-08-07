<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PromptActionRequest extends FormRequest
{
    public function authorize(): bool
    {
    // Routes are already protected by auth middleware; ensure user is present.
    return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'action' => 'required|string|in:reformulate_title,summarize,assign_activity,assign_thesaurus,index_thesaurus,index_theaurus,summarize_slip',
            'entity' => 'required|string|in:record,mail,communication,slip_record',
            'entity_ids' => 'required|array|min:1',
            'entity_ids.*' => 'integer|min:1',
            'context' => 'sometimes|array',
            'model' => 'sometimes|string',
            'model_provider' => 'sometimes|string|in:ollama,openai,gemini,claude,openrouter,onn,ollama_turbo,openai_custom',
            'confirm' => 'sometimes|boolean',
            'stream' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => 'L\'action est obligatoire.',
            'action.in' => 'Action non supportée.',
            'entity.required' => 'L\'entité est obligatoire.',
            'entity.in' => 'Entité non supportée.',
            'entity_ids.required' => 'Au moins un identifiant d\'entité est requis.',
            'entity_ids.array' => 'Les identifiants doivent être un tableau.',
            'entity_ids.*.integer' => 'Chaque identifiant doit être un entier.',
            'model_provider.in' => 'Provider non supporté.',
        ];
    }
}
