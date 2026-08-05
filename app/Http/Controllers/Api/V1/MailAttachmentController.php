<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MailAttachmentResource;
use App\Models\MailAttachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D06 — relu et validé le 2026-08-04 contre `MailAttachmentController` et le schéma.
 *
 * Les pièces jointes de courrier (`attachments`, table partagée avec les enregistrements)
 * sont exposées en **ressource plate** avec le filtre `?filter[mail_id]=` (les sous-ressources
 * imbriquées du Blade `mails/file/{file}/attachment` restent plates — CONVENTIONS).
 * Elles sont **org-scopées** (R03) via leurs courriers rattachés (`Mail::inOrganisation`).
 *
 * TODO — actions non portées :
 *   - **store (téléversement)** : `MailAttachmentController::store` — stockage sur disque,
 *     calculs MD5/SHA-512, miniatures Image/FFMpeg et rattachement au courrier via le
 *     pivot `mail_attachment` (`added_by`). Le flux complet (upload + thumbnail) est un
 *     workflow à porter avec la création de courrier (voir `MailController`).
 *   - `download` / `preview` : accès binaire aux fichiers — type « export », non porté.
 *   - **destroy** : le Blade supprime aussi le fichier physique (Storage) ; l'API détache
 *     et supprime l'enregistrement, le nettoyage des fichiers orphelins reste à la charge
 *     de l'outillage dédié (`MailSendController::cleanupOrphanedAttachments`).
 */
class MailAttachmentController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'name', 'path', 'size', 'mime_type', 'type', 'creator_id', 'is_primary', 'display_order', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'name', 'size', 'creator_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['creator', 'mails'];

    /**
     * GET /api/v1/mail-attachments?filter[mail_id]=
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', MailAttachment::class);

        $query = MailAttachment::inOrganisation(Auth::user()->current_organisation_id);

        // Filtre spécifique `mail_id` : colonne du pivot `mail_attachment`, pas de la table.
        $filters = $request->input('filter', []);
        if (is_array($filters) && array_key_exists('mail_id', $filters)) {
            $query->whereHas('mails', fn ($q) => $q->whereKey($filters['mail_id']));
            $request->merge(['filter' => array_diff_key($filters, ['mail_id' => true])]);
        }

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, MailAttachmentResource::class));
    }

    /**
     * GET /api/v1/mail-attachments/{id}
     */
    public function show(MailAttachment $mailAttachment): JsonResponse
    {
        $this->authorize('view', $mailAttachment);

        // Isolation R03 : une pièce jointe hors du périmètre organisation est 404.
        $mailAttachment = MailAttachment::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($mailAttachment->id);

        return response()->json(['data' => new MailAttachmentResource($mailAttachment)]);
    }

    /**
     * POST /api/v1/mail-attachments
     *
     * TODO — téléversement non porté (voir l'en-tête de ce contrôleur) : stockage,
     * empreintes, miniatures et rattachement au courrier.
     */
    public function store(): JsonResponse
    {
        abort(501, 'Le téléversement de pièces jointes n\'est pas encore exposé par l\'API v1.');
    }

    /**
     * DELETE /api/v1/mail-attachments/{id}
     */
    public function destroy(MailAttachment $mailAttachment): Response
    {
        $this->authorize('delete', $mailAttachment);

        $mailAttachment = MailAttachment::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($mailAttachment->id);

        // TODO : suppression du fichier physique (Storage) — voir l'en-tête.
        $mailAttachment->mails()->detach();
        $mailAttachment->delete();

        return response()->noContent();
    }
}
