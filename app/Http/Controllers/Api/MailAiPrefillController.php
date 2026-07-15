<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Services\AI\MailPrefillService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class MailAiPrefillController extends Controller
{
    /**
     * Upload d'une pièce jointe AVANT la création du courrier, pour analyse
     * IA. L'Attachment reste "orphelin" (aucun lien mail) jusqu'au submit
     * final du formulaire, où son id est renvoyé dans attachments_pending[].
     */
    public function upload(Request $request)
    {
        Gate::authorize('mail_create');

        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ]);

        $attachment = Attachment::createFromUpload($request->file('file'), Attachment::TYPE_ATTACHMENT, Auth::id());

        return response()->json([
            'success' => true,
            'attachment_id' => $attachment->id,
            'name' => $attachment->name,
            'size' => $attachment->size,
        ], 201);
    }

    /**
     * Propose un préremplissage à partir d'une pièce jointe déjà uploadée.
     * Ne modifie rien : l'utilisateur reste seul décisionnaire.
     */
    public function suggest(Request $request, MailPrefillService $prefill)
    {
        Gate::authorize('mail_create');

        $validated = $request->validate([
            'attachment_id' => 'required|integer|exists:attachments,id',
            'context' => 'required|in:received,send,received_external,send_external',
        ]);

        $attachment = Attachment::query()
            ->where('id', $validated['attachment_id'])
            ->where('creator_id', Auth::id())
            ->first();

        if (!$attachment) {
            return response()->json(['status' => 'error', 'message' => 'Pièce jointe introuvable.'], 403);
        }

        try {
            $result = $prefill->suggest($attachment, $validated['context']);
        } catch (\Throwable $e) {
            Log::error('MailAiPrefillController: échec suggestion', ['error' => $e->getMessage()]);
            $result = ['status' => 'error', 'message' => "Une erreur est survenue pendant l'analyse. Merci de remplir le formulaire manuellement."];
        }

        return response()->json($result);
    }
}
