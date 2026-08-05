<?php

namespace App\Http\Requests\Api\Public;

use Illuminate\Foundation\Http\FormRequest;

/**
 * D15 — soumission de feedback par un usager public connecté. Aligné sur le
 * schéma `public_feedbacks` (enums `type` et `priority`) ; `user_id` et
 * `status` sont déduits côté serveur, jamais fournis par le client.
 */
class StoreFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'type' => ['required', 'string', 'in:bug,feature,improvement,other'],
            'priority' => ['required', 'string', 'in:low,medium,high'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
        ];
    }
}
