<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MailArchive\StoreMailArchiveRequest;
use App\Http\Requests\Api\V1\MailArchive\UpdateMailArchiveRequest;
use App\Http\Resources\Api\V1\MailArchiveResource;
use App\Models\MailArchive;
use App\Models\MailContainer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D06 — relu et validé le 2026-08-04 contre `MailArchiveController` et le schéma.
 *
 * Les archives de courrier (`mail_archives`) sont **org-scopées** (R03) via le contenant
 * qui les possède (`mail_containers.creator_organisation_id`) : l'index est borné aux
 * contenants de l'organisation courante, et toute ressource hors périmètre répond 404
 * (jamais 403). `archived_by` est posé depuis l'agent authentifié.
 *
 * TODO — actions métier complexes non portées :
 *   - **store en masse / workflow d'archivage** : `MailArchiveController::store` /
 *     `addMails` / `removeMails` (archivage de plusieurs courriers d'un coup + bascule
 *     `mails.is_archived`), `MailTransactionController::archive` (contrôle des statuts
 *     `in_progress`/`reject`, dédoublonnage, marquage `is_archived`).
 *   - `MailArchiveController::archived` / `getAvailableMailsForArchive` /
 *     `getArchivedMails` : vues/listes dédiées (couvertes par les filtres de l'index
 *     `?filter[container_id]=` et par la ressource `mails`).
 *   - **destroy** : le Blade ne bascule pas `mails.is_archived` à faux en désarchivant —
 *     comportement conservé, à réconcilier lors du portage du workflow d'archivage.
 */
class MailArchiveController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'container_id', 'mail_id', 'archived_by', 'document_type', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'container_id', 'mail_id', 'archived_by', 'document_type', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['container', 'mail', 'user'];

    /**
     * GET /api/v1/mail-archives
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', MailArchive::class);

        $query = MailArchive::inOrganisation(Auth::user()->current_organisation_id);

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, MailArchiveResource::class));
    }

    /**
     * GET /api/v1/mail-archives/{id}
     */
    public function show(MailArchive $mailArchive): JsonResponse
    {
        $this->authorize('view', $mailArchive);

        // Isolation R03 : une archive hors de l'organisation courante est 404.
        $mailArchive = MailArchive::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($mailArchive->id);

        return response()->json(['data' => new MailArchiveResource($mailArchive)]);
    }

    /**
     * POST /api/v1/mail-archives
     */
    public function store(StoreMailArchiveRequest $request): JsonResponse
    {
        $this->authorize('create', MailArchive::class);

        // Une archive doit être posée dans un contenant de l'organisation courante,
        // sinon elle serait créée hors de portée de son propre créateur.
        if (!MailContainer::inOrganisation(Auth::user()->current_organisation_id)->whereKey($request->input('container_id'))->exists()) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Validation', 'status' => 422, 'detail' => 'Le contenant n\'appartient pas à votre organisation.', 'errors' => ['container_id' => ['Le contenant n\'appartient pas à votre organisation.']]],
                422
            );
        }

        $mailArchive = MailArchive::create($request->validated() + ['archived_by' => Auth::id()]);

        return response()->json(
            ['data' => new MailArchiveResource($mailArchive)],
            201,
            ['Location' => "/api/v1/mail-archives/{$mailArchive->id}"]
        );
    }

    /**
     * PATCH /api/v1/mail-archives/{id}
     */
    public function update(UpdateMailArchiveRequest $request, MailArchive $mailArchive): JsonResponse
    {
        $this->authorize('update', $mailArchive);

        $mailArchive = MailArchive::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($mailArchive->id);

        $mailArchive->update($request->validated());

        return response()->json(['data' => new MailArchiveResource($mailArchive->fresh())]);
    }

    /**
     * DELETE /api/v1/mail-archives/{id}
     */
    public function destroy(MailArchive $mailArchive): Response
    {
        $this->authorize('delete', $mailArchive);

        $mailArchive = MailArchive::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($mailArchive->id);

        $mailArchive->delete();

        return response()->noContent();
    }
}
