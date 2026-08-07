<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\MailStatusEnum;
use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Mail\StoreMailRequest;
use App\Http\Requests\Api\V1\Mail\UpdateMailRequest;
use App\Http\Resources\Api\V1\MailResource;
use App\Models\Mail;
use App\Models\MailTypology;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D06 — relu et validé le 2026-08-04 contre `MailController`, `MailReceivedController`,
 * `MailSendController`, `MailReceivedExternalController`, `MailSendExternalController`
 * et le schéma.
 *
 * Les courriers sont **org-scopés** (R03) via les colonnes `sender_organisation_id`,
 * `recipient_organisation_id` et `assigned_organisation_id` (modèle dual-organisation) :
 * l'index ne renvoie que les courriers impliquant l'organisation courante, et toute
 * ressource hors périmètre répond 404 (jamais 403).
 *
 * Porté le 2026-08-05 — la **création de courrier** (`POST /mails`, sans téléversement)
 * est exposée : génération séquentielle du code `{année}/{code_typologie}/{numéro}`,
 * statuts entrant (`transmitted`) / sortant (`draft`) et relations expéditeur/destinataire
 * (`external_*`, organisation tierce) reprises de `MailController::storeIncoming()` /
 * `storeOutgoing()`. Règles dans `StoreMailRequest`.
 *
 * TODO — actions métier complexes non portées, à documenter en phase 2 :
 *   - **téléversement de pièces jointes** : stockage, MD5/SHA-512, miniatures
 *     Image/FFMpeg (`MailController::handleFileUpload`) — exposé par la sous-ressource
 *     `mail-attachments`, dont le `store` est lui-même en TODO (E2, phase 3).
 *   - **workflows de statut** : `MailReceivedController::approve` / `reject` /
 *     `inprogress` / `toReturn` / `returned`, `MailSendController::approve` /
 *     `transfer` / `inprogress` / `rejected` — transitions d'état à porter par une
 *     action dédiée (ex. `POST /mails/{mail}/status`).
 *   - **destroy** : la suppression des fichiers physiques des pièces jointes
 *     (Storage::delete) n'est pas reprise — l'API supprime l'enregistrement, les
 *     fichiers orphelins seront traités par le nettoyage dédié du Blade.
 *   - `MailTransactionController::export` / `print` / `import` / `archive` :
 *     exports Excel/PDF et archivage en masse (la table `mail_transactions` est
 *     absente du schéma — voir la note de portage du parapheur).
 *
 * Divergences documentées :
 *   - `countUnread` : le Blade référence `MailStatusEnum::RECEIVED`, constante inexistante
 *     (fatal PHP latent) ; l'intention est reprise en comptant les courriers entrants non
 *     traités (`processed_at` NULL), sans le statut inexistant.
 */
class MailController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = [
        'id', 'code', 'name', 'date', 'document_type', 'status', 'mail_type',
        'priority_id', 'typology_id', 'action_id', 'is_archived',
        'sender_user_id', 'sender_organisation_id', 'recipient_user_id', 'recipient_organisation_id',
        'assigned_to', 'assigned_organisation_id', 'external_sender_id', 'external_recipient_id',
        'deadline', 'created_at', 'updated_at',
    ];
    private const SORTABLE = [
        'id', 'code', 'name', 'date', 'document_type', 'status', 'mail_type',
        'priority_id', 'typology_id', 'action_id', 'is_archived',
        'sender_organisation_id', 'recipient_organisation_id', 'deadline',
        'created_at', 'updated_at',
    ];
    private const INCLUDABLE = [
        'priority', 'typology', 'action', 'sender', 'recipient',
        'senderOrganisation', 'recipientOrganisation',
        'externalSender', 'externalSenderOrganization',
        'externalRecipient', 'externalRecipientOrganization',
        'assignedTo', 'assignedOrganisation',
        'attachments', 'containers', 'batches', 'authors', 'histories',
    ];

    /**
     * GET /api/v1/mails
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Mail::class);

        $query = Mail::inOrganisation(Auth::user()->current_organisation_id);

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, MailResource::class));
    }

    /**
     * GET /api/v1/mails/{id}
     */
    public function show(Mail $mail, Request $request): JsonResponse
    {
        $this->authorize('view', $mail);

        // Isolation R03 : un courrier hors de l'organisation courante est 404.
        $query = Mail::inOrganisation(Auth::user()->current_organisation_id);

        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $mail = $query->findOrFail($mail->id);

        return response()->json(['data' => new MailResource($mail)]);
    }

    /**
     * POST /api/v1/mails
     *
     * Création de courrier portée (2026-08-05) sans téléversement de pièces jointes :
     * voir l'en-tête de ce contrôleur et `StoreMailRequest`. `mail_type` (incoming /
     * outgoing) décide du rôle de l'organisation courante et des relations
     * expéditeur/destinataire posées côté serveur.
     */
    public function store(StoreMailRequest $request): JsonResponse
    {
        $this->authorize('create', Mail::class);

        $validated = $request->validated();
        $mailType = $validated['mail_type'] ?? Mail::TYPE_INCOMING;
        unset($validated['mail_type']);

        // Code séquentiel `{année}/{code_typologie}/{numéro}` — sauf code fourni (sortant).
        if (empty($validated['code'])) {
            $validated['code'] = $this->generateMailCode($validated['typology_id']);
        }

        if ($mailType === Mail::TYPE_INCOMING) {
            $mail = Mail::create($validated + [
                'recipient_organisation_id' => Auth::user()->current_organisation_id,
                'recipient_user_id' => Auth::id(),
                'recipient_type' => 'organisation',
                'status' => MailStatusEnum::TRANSMITTED,
                'mail_type' => Mail::TYPE_INCOMING,
            ]);
        } else {
            $mail = Mail::create($validated + [
                'sender_organisation_id' => Auth::user()->current_organisation_id,
                'sender_user_id' => Auth::id(),
                'sender_type' => 'organisation',
                'status' => MailStatusEnum::DRAFT,
                'mail_type' => Mail::TYPE_OUTGOING,
            ]);
        }

        $mail->logAction('created', null, null, null, $mailType === Mail::TYPE_INCOMING ? 'Courrier entrant créé' : 'Courrier sortant créé');

        return response()->json(
            ['data' => new MailResource($mail->fresh())],
            201,
            ['Location' => "/api/v1/mails/{$mail->id}"]
        );
    }

    /**
     * Génère le code séquentiel d'un courrier : `{année}/{code_typologie}/{numéro}` à
     * 4 chiffres, numérotation par typologie et par année, sans collision (reprise de
     * `MailController::generateMailCode()`).
     */
    private function generateMailCode(int $typologyId): string
    {
        $typology = MailTypology::findOrFail($typologyId);
        $year = now()->year;

        $nextNumber = Mail::whereYear('created_at', $year)
            ->where('typology_id', $typologyId)
            ->count() + 1;

        while (true) {
            $candidate = $year . '/' . $typology->code . '/' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);

            if (!Mail::where('code', $candidate)->exists()) {
                return $candidate;
            }

            $nextNumber++;
        }
    }

    /**
     * PATCH /api/v1/mails/{id}
     */
    public function update(UpdateMailRequest $request, Mail $mail): JsonResponse
    {
        $this->authorize('update', $mail);

        $mail = Mail::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($mail->id);

        $mail->update($request->validated());

        $mail->logAction('updated', null, null, null, 'Courrier mis à jour');

        return response()->json(['data' => new MailResource($mail->fresh())]);
    }

    /**
     * DELETE /api/v1/mails/{id}
     */
    public function destroy(Mail $mail): Response
    {
        $this->authorize('delete', $mail);

        $mail = Mail::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($mail->id);

        // TODO : supprimer aussi les fichiers physiques des pièces jointes (voir l'en-tête).
        $mail->delete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/mails/count-unread
     *
     * Nombre de courriers entrants non traités de l'organisation courante — reprise de
     * `MailController::countUnread()` (voir la divergence documentée en tête de contrôleur).
     */
    public function countUnread(): JsonResponse
    {
        $this->authorize('viewAny', Mail::class);

        $count = Mail::inOrganisation(Auth::user()->current_organisation_id)
            ->incoming()
            ->where('status', MailStatusEnum::TRANSMITTED)
            ->whereNull('processed_at')
            ->count();

        return response()->json(['count' => $count]);
    }
}
