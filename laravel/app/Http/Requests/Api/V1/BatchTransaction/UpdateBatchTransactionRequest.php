<?php

namespace App\Http\Requests\Api\V1\BatchTransaction;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'une transaction de parapheur — D06.
 *
 * Les colonnes `organisation_send_id` / `organisation_received_id` sont gérées côté
 * serveur (l'organisation courante selon le rôle du flux), jamais acceptées du client.
 * Seul `batch_id` est modifiable, comme dans les `update()` Blade (`BatchReceivedController`
 * / `BatchSendController`).
 */
class UpdateBatchTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
            'batch_id' => 'sometimes|required|exists:batches,id',
        ];
    }
}
