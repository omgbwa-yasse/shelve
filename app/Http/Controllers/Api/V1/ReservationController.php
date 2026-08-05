<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Reservation\StoreReservationRequest;
use App\Http\Requests\Api\V1\Reservation\UpdateReservationRequest;
use App\Http\Resources\Api\V1\ReservationResource;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D05 — relu et validé le 2026-08-04 contre `ReservationController` et le schéma.
 *
 * Les réservations sont **org-scopées** par leurs deux clés d'organisation
 * (`operator_organisation_id` / `user_organisation_id`, motif D03) : l'index n'expose
 * que les réservations impliquant l'organisation courante, et une ressource hors
 * périmètre répond 404. `code` est généré serveur, `operator_id` /
 * `operator_organisation_id` posés depuis l'agent authentifié.
 *
 * TODO (non portés en phase 1) :
 *  - `approved` : conversion d'une réservation approuvée en communication — action
 *    multi-étapes (transaction DB, création d'une `Communication` + pivots) avec
 *    services dédiés (`CodeGeneratorService`).
 *  - `listApproved` / `listApprovedReservations` / `pending` / `returnAvailable` :
 *    vues de listes — couvertes par le filtre `status` de l'index.
 */
class ReservationController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'code', 'name', 'operator_organisation_id', 'user_id', 'user_organisation_id', 'status', 'return_date', 'return_effective', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'code', 'name', 'operator_organisation_id', 'user_id', 'user_organisation_id', 'status', 'return_date', 'return_effective', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['operator', 'user', 'userOrganisation', 'operatorOrganisation', 'communication', 'records'];

    /**
     * GET /api/v1/reservations
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Reservation::class);

        $query = Reservation::inOrganisation(Auth::user()->current_organisation_id);

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, ReservationResource::class));
    }

    /**
     * GET /api/v1/reservations/{id}
     */
    public function show(Reservation $reservation): JsonResponse
    {
        $this->authorize('view', $reservation);

        $reservation = Reservation::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($reservation->id);

        return response()->json(['data' => new ReservationResource($reservation)]);
    }

    /**
     * POST /api/v1/reservations
     */
    public function store(StoreReservationRequest $request): JsonResponse
    {
        $this->authorize('create', Reservation::class);

        // Comme en Blade : le code est généré serveur.
        $reservation = Reservation::create($request->validated() + [
            'code' => (new \App\Services\CodeGeneratorService())->generateReservationCode(),
            'operator_id' => Auth::id(),
            'operator_organisation_id' => Auth::user()->current_organisation_id,
        ]);

        return response()->json(
            ['data' => new ReservationResource($reservation)],
            201,
            ['Location' => "/api/v1/reservations/{$reservation->id}"]
        );
    }

    /**
     * PUT /api/v1/reservations/{id}
     */
    public function update(UpdateReservationRequest $request, Reservation $reservation): JsonResponse
    {
        $this->authorize('update', $reservation);

        $reservation = Reservation::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($reservation->id);

        $reservation->update($request->validated() + [
            'operator_id' => Auth::id(),
            'operator_organisation_id' => Auth::user()->current_organisation_id,
        ]);

        return response()->json(['data' => new ReservationResource($reservation->fresh())]);
    }

    /**
     * DELETE /api/v1/reservations/{id}
     */
    public function destroy(Reservation $reservation): Response
    {
        $this->authorize('delete', $reservation);

        $reservation = Reservation::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($reservation->id);

        $reservation->delete();

        return response()->noContent();
    }

    /**
     * POST /api/v1/reservations/{reservation}/mark-returned — marquer comme retournée.
     */
    public function markAsReturned(Reservation $reservation): JsonResponse
    {
        $this->authorize('update', $reservation);

        $reservation = Reservation::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($reservation->id);

        $reservation->update(['return_effective' => now()->format('Y-m-d')]);

        // Comme en Blade : mettre aussi à jour la communication associée si elle existe.
        if ($reservation->communication) {
            $reservation->communication->update(['return_effective' => now()->format('Y-m-d')]);
        }

        return response()->json(['data' => new ReservationResource($reservation->fresh())]);
    }
}
