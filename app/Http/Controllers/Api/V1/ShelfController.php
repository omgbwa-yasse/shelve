<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Shelf\StoreShelfRequest;
use App\Http\Requests\Api\V1\Shelf\UpdateShelfRequest;
use App\Http\Resources\Api\V1\ShelfResource;
use App\Models\Room;
use App\Models\Shelf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D03 — relu et validé le 2026-08-04 contre `ShelfController` et le schéma.
 *
 * Les rayonnages héritent de l'organisation de leur salle (R03) : l'index est borné
 * aux salles de l'organisation courante, et une ressource hors périmètre répond 404.
 * `creator_id` posé depuis l'agent.
 */
class ShelfController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'code', 'room_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'code', 'room_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['room', 'containers', 'creator'];

    /**
     * GET /api/v1/shelves
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Shelf::class);

        $query = Shelf::inOrganisation(Auth::user()->current_organisation_id)
            ->withCount('containers');

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, ShelfResource::class));
    }

    /**
     * GET /api/v1/shelves/{id}
     */
    public function show(Shelf $shelf): JsonResponse
    {
        $this->authorize('view', $shelf);

        $shelf = Shelf::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($shelf->id);

        return response()->json(['data' => new ShelfResource($shelf)]);
    }

    /**
     * POST /api/v1/shelves
     */
    public function store(StoreShelfRequest $request): JsonResponse
    {
        $this->authorize('create', Shelf::class);

        // Un rayonnage doit être rattaché à une salle de l'organisation courante,
        // sinon il serait créé hors de portée de son propre créateur.
        if (!Room::inOrganisation(Auth::user()->current_organisation_id)->whereKey($request->input('room_id'))->exists()) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Validation', 'status' => 422, 'detail' => 'La salle n\'appartient pas à votre organisation.', 'errors' => ['room_id' => ['La salle n\'appartient pas à votre organisation.']]],
                422
            );
        }

        $shelf = Shelf::create($request->validated() + ['creator_id' => Auth::id()]);

        return response()->json(
            ['data' => new ShelfResource($shelf)],
            201,
            ['Location' => "/api/v1/shelves/{$shelf->id}"]
        );
    }

    /**
     * PATCH /api/v1/shelves/{id}
     */
    public function update(UpdateShelfRequest $request, Shelf $shelf): JsonResponse
    {
        $this->authorize('update', $shelf);

        $shelf = Shelf::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($shelf->id);

        $shelf->update($request->validated());

        return response()->json(['data' => new ShelfResource($shelf->fresh())]);
    }

    /**
     * DELETE /api/v1/shelves/{id}
     */
    public function destroy(Shelf $shelf): Response
    {
        $this->authorize('delete', $shelf);

        $shelf = Shelf::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($shelf->id);

        $shelf->delete();

        return response()->noContent();
    }
}
