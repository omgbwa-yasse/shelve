<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Room\StoreRoomRequest;
use App\Http\Requests\Api\V1\Room\UpdateRoomRequest;
use App\Http\Resources\Api\V1\RoomResource;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D03 — relu et validé le 2026-08-04 contre `RoomController` et le schéma.
 *
 * Les salles sont **org-scopées** via la pivot `organisation_room` (R03) : l'index
 * ne renvoie que les salles de l'organisation courante, et toute ressource d'une
 * autre organisation répond 404 (jamais 403 — voir CONVENTIONS §4).
 * `creator_id` posé depuis l'agent.
 */
class RoomController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'code', 'name', 'visibility', 'type', 'floor_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'code', 'name', 'visibility', 'type', 'floor_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['floor', 'shelves', 'organisations'];

    /**
     * GET /api/v1/rooms
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Room::class);

        $query = Room::inOrganisation(Auth::user()->current_organisation_id);

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, RoomResource::class));
    }

    /**
     * GET /api/v1/rooms/{id}
     */
    public function show(Room $room, Request $request): JsonResponse
    {
        $this->authorize('view', $room);

        // Isolation R03 : une salle hors de l'organisation courante est 404.
        $room = Room::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($room->id);

        return response()->json(['data' => new RoomResource($room)]);
    }

    /**
     * POST /api/v1/rooms
     */
    public function store(StoreRoomRequest $request): JsonResponse
    {
        $this->authorize('create', Room::class);

        $room = Room::create($request->validated() + ['creator_id' => Auth::id()]);

        // Comme en Blade : la salle est rattachée à l'organisation courante de l'agent.
        $room->organisations()->attach(Auth::user()->current_organisation_id);

        return response()->json(
            ['data' => new RoomResource($room->fresh())],
            201,
            ['Location' => "/api/v1/rooms/{$room->id}"]
        );
    }

    /**
     * PATCH /api/v1/rooms/{id}
     */
    public function update(UpdateRoomRequest $request, Room $room): JsonResponse
    {
        $this->authorize('update', $room);

        $room = Room::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($room->id);

        $room->update($request->validated());

        return response()->json(['data' => new RoomResource($room->fresh())]);
    }

    /**
     * DELETE /api/v1/rooms/{id}
     */
    public function destroy(Room $room): Response
    {
        $this->authorize('delete', $room);

        $room = Room::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($room->id);

        $room->delete();

        return response()->noContent();
    }
}
