<?php

namespace App\Http\Requests\Api\Public;

use Illuminate\Foundation\Http\FormRequest;

/**
 * D15 — inscription d'un usager public à un événement. `event_id` et `user_id`
 * viennent de la route et du token : rien à valider ici d'autre que `notes`.
 */
class StoreEventRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
