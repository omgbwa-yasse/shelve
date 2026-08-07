<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MailContainer\StoreMailContainerRequest;
use App\Http\Requests\Api\V1\MailContainer\UpdateMailContainerRequest;
use App\Http\Resources\Api\V1\MailContainerResource;
use App\Models\ContainerProperty;
use App\Models\MailContainer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D06 — relu et validé le 2026-08-04 contre `MailContainerController` et le schéma.
 *
 * Les contenants de courrier sont **org-scopés** via `creator_organisation_id` (R03) :
 * l'index est borné à l'organisation courante, et toute ressource hors périmètre répond
 * 404 (jamais 403). `created_by` et `creator_organisation_id` sont posés depuis l'agent.
 * La garde du Blade « un contenant contenant des archives ne se supprime pas » est reprise
 * (409).
 *
 * Actions non-CRUD du Blade :
 *   - `getContainers` : liste simplifiée (id, code, name) de l'organisation — portée en
 *     action `GET /api/v1/mail-containers/list` (une liste réduite équivalente est aussi
 *     obtenue par `GET /mails` avec filtres).
 *   - `getContainerProperties` : renvoie les propriétés de contenant — NON portée ici,
 *     déjà couverte par la ressource D03 `container-properties`.
 *   - `MailContainerTransferController::transfer` / `getActivitiesByOrganisation` /
 *     `getShelvesByOrganisation` : transfert de contenants vers un slip + création de
 *     conteneurs/SlipRecords — TODO, workflow courrier multi-étapes (voir l'en-tête).
 */
class MailContainerController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'code', 'name', 'property_id', 'created_by', 'creator_organisation_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'code', 'name', 'property_id', 'created_by', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['containerProperty', 'creator', 'organisation', 'mails'];

    /**
     * GET /api/v1/mail-containers
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', MailContainer::class);

        $query = MailContainer::inOrganisation(Auth::user()->current_organisation_id)
            ->withCount('mails');

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, MailContainerResource::class));
    }

    /**
     * GET /api/v1/mail-containers/{id}
     */
    public function show(MailContainer $mailContainer, Request $request): JsonResponse
    {
        $this->authorize('view', $mailContainer);

        // Isolation R03 : un contenant hors de l'organisation courante est 404.
        $query = MailContainer::inOrganisation(Auth::user()->current_organisation_id)
            ->withCount('mails');

        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $mailContainer = $query->findOrFail($mailContainer->id);

        return response()->json(['data' => new MailContainerResource($mailContainer)]);
    }

    /**
     * POST /api/v1/mail-containers
     */
    public function store(StoreMailContainerRequest $request): JsonResponse
    {
        $this->authorize('create', MailContainer::class);

        $mailContainer = MailContainer::create($request->validated() + [
            'created_by' => Auth::id(),
            'creator_organisation_id' => Auth::user()->current_organisation_id,
        ]);

        return response()->json(
            ['data' => new MailContainerResource($mailContainer)],
            201,
            ['Location' => "/api/v1/mail-containers/{$mailContainer->id}"]
        );
    }

    /**
     * PATCH /api/v1/mail-containers/{id}
     */
    public function update(UpdateMailContainerRequest $request, MailContainer $mailContainer): JsonResponse
    {
        $this->authorize('update', $mailContainer);

        $mailContainer = MailContainer::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($mailContainer->id);

        $mailContainer->update($request->validated());

        return response()->json(['data' => new MailContainerResource($mailContainer->fresh())]);
    }

    /**
     * DELETE /api/v1/mail-containers/{id}
     */
    public function destroy(MailContainer $mailContainer): JsonResponse|Response
    {
        $this->authorize('delete', $mailContainer);

        $mailContainer = MailContainer::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($mailContainer->id);

        // Reprise de la garde du Blade : un contenant avec des archives ne se supprime pas.
        if ($mailContainer->mails()->exists()) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Conflit', 'status' => 409, 'detail' => 'Impossible de supprimer un contenant contenant des courriers archivés.'],
                409
            );
        }

        $mailContainer->delete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/mail-containers/list
     *
     * Liste simplifiée des contenants de l'organisation courante — reprise de
     * `MailContainerController::getContainers()` (id, code, name).
     */
    public function getContainers(): JsonResponse
    {
        $this->authorize('viewAny', MailContainer::class);

        $containers = MailContainer::inOrganisation(Auth::user()->current_organisation_id)
            ->select('id', 'code', 'name')
            ->orderBy('code')
            ->get();

        return response()->json(['data' => $containers]);
    }

    /**
     * GET /api/v1/mail-containers/properties
     *
     * Propriétés de contenant utilisables — reprise de `getContainerProperties()`.
     * Note : la ressource D03 `container-properties` offre le CRUD complet.
     */
    public function getContainerProperties(): JsonResponse
    {
        $this->authorize('viewAny', ContainerProperty::class);

        $properties = ContainerProperty::select('id', 'name')->orderBy('name')->get();

        return response()->json(['data' => $properties]);
    }
}
