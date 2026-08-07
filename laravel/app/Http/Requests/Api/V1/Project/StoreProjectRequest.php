<?php

namespace App\Http\Requests\Api\V1\Project;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un projet — D17.
 *
 * `attachable_type` est un alias court ("workplace"|"organisation"|"user"),
 * jamais un FQCN PHP (voir App\Traits\HasAttachable::attachableAliases()).
 * `organisation_id`, `created_by`, `updated_by` sont posés depuis l'agent.
 */
class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:30|unique:projects,code',
            'name' => 'required|string|max:190',
            'description' => 'nullable|string',
            'status' => 'sometimes|string|in:draft,active,on_hold,completed,archived',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'owner_id' => 'nullable|integer|exists:users,id',
            'attachable_type' => 'required|string|in:' . implode(',', array_keys(Project::attachableAliases())),
            'attachable_id' => 'required|integer',
        ];
    }
}
