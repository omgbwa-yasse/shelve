<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Organisation\StoreOrganisationRequest;
use App\Http\Requests\Api\V1\Organisation\UpdateOrganisationRequest;
use App\Http\Resources\Api\V1\OrganisationResource;
use App\Models\Organisation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D09 — organisations, relu le 2026-08-05 contre `OrganisationController` et le schéma.
 *
 * Les organisations sont un référentiel GLOBAL (hierarchie `parent_id`) : pas
 * d'isolation par organisation — ce sont elles qui définissent le périmètre.
 * L'action `switchOrganisation` (changement d'organisation courante) est déjà
 * portée dans AuthController (POST /api/v1/auth/switch-organisation). Les exports
 * Excel/PDF (`exportExcel`/`exportPdf`) et la construction de la hiérarchie côté
 * vue ne sont pas des ressources REST : non portés (voir abandon D09).
 */
class OrganisationController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'code', 'name', 'parent_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'code', 'name', 'parent_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['parent', 'children'];

    /**
     * GET /api/v1/organisations
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Organisation::class);

        $query = Organisation::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE, 'code');
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, OrganisationResource::class));
    }

    /**
     * GET /api/v1/organisations/{id}
     */
    public function show(Organisation $organisation): JsonResponse
    {
        $this->authorize('view', $organisation);

        return response()->json(['data' => new OrganisationResource($organisation)]);
    }

    /**
     * POST /api/v1/organisations
     */
    public function store(StoreOrganisationRequest $request): JsonResponse
    {
        $this->authorize('create', Organisation::class);

        $organisation = Organisation::create($request->validated());

        return response()->json(
            ['data' => new OrganisationResource($organisation)],
            201,
            ['Location' => "/api/v1/organisations/{$organisation->id}"]
        );
    }

    /**
     * PATCH /api/v1/organisations/{id}
     */
    public function update(UpdateOrganisationRequest $request, Organisation $organisation): JsonResponse
    {
        $this->authorize('update', $organisation);

        $organisation->update($request->validated());

        return response()->json(['data' => new OrganisationResource($organisation->fresh())]);
    }

    /**
     * DELETE /api/v1/organisations/{id}
     */
    public function destroy(Organisation $organisation): Response
    {
        $this->authorize('delete', $organisation);

        $organisation->delete();

        return response()->noContent();
    }
}
