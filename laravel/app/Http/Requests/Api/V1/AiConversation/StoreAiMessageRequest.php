<?php

namespace App\Http\Requests\Api\V1\AiConversation;

use App\Models\AiConversation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAiMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => 'required|string',
            'context' => 'nullable|array',
            // Permet de changer le mode en cours de fil (voir AiConversation::MODES).
            'mode' => ['nullable', 'string', Rule::in(AiConversation::MODES)],
        ];
    }
}
