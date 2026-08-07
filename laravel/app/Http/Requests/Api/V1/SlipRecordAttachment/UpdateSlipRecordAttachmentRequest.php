<?php

namespace App\Http\Requests\Api\V1\SlipRecordAttachment;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'une pièce jointe de document de bordereau — D04.
 *
 * Le Blade n'expose aucune mise à jour d'une pivot `slip_record_attachments` : seuls
 * l'upload et la suppression existent. Cette requête reste disponible mais sans règle
 * active, pour ne pas inventer de contrat absent du contrôleur source.
 */
class UpdateSlipRecordAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
