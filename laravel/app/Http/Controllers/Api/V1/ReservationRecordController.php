<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ReservationRecord\StoreReservationRecordRequest;
use App\Http\Requests\Api\V1\ReservationRecord\UpdateReservationRecordRequest;
use App\Http\Resources\Api\V1\ReservationRecordResource;
use App\Models\Reservation;
use App\Models\ReservationRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D05 — relu et validé le 2026-08-04 contre `ReservationRecordController` et le schéma.
 *
 * Les documents de réservation sont **org-scopés** par leur réservation parente (motif
 * D03) : la `Reservation` est résolue dans l'organisation courante, puis chaque
 * ressource est bornée à `reservation_id`. `reservation_id` vient de la route et
 * `operator_id` est posé depuis l'agent authentifié. Le Blade référençait
 * `ReservationRecordPhysical` (classe inexistante) : corrigé avec le modèle réel.
 */
class ReservationRecordController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'reservation_id', 'record_id', 'is_original', 'reservation_date', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'reservation_id', 'record_id', 'is_original', 'reservation_date', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['reservation', 'record', 'communication', 'operator'];

    /**
     * GET /api/v1/reservations/{reservation}/records
     */
    public function index(Reservation $reservation, Request $request): JsonResponse
    {
        $this->authorize('viewAny', ReservationRecord::class);

        $reservation = Reservation::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($reservation->id);

        $query = ReservationRecord::where('reservation_id', $reservation->id);

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, ReservationRecordResource::class));
    }

    /**
     * GET /api/v1/reservations/{reservation}/records/{id}
     */
    public function show(Reservation $reservation, ReservationRecord $reservationRecord): JsonResponse
    {
        $this->authorize('view', $reservationRecord);

        $reservation = Reservation::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($reservation->id);
        $reservationRecord = $this->resolveRecord($reservation, $reservationRecord->id);

        return response()->json(['data' => new ReservationRecordResource($reservationRecord)]);
    }

    /**
     * POST /api/v1/reservations/{reservation}/records
     */
    public function store(StoreReservationRecordRequest $request, Reservation $reservation): JsonResponse
    {
        $this->authorize('create', ReservationRecord::class);

        $reservation = Reservation::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($reservation->id);

        $reservationRecord = ReservationRecord::create($request->validated() + [
            'reservation_id' => $reservation->id,
            'operator_id' => Auth::id(),
        ]);

        return response()->json(
            ['data' => new ReservationRecordResource($reservationRecord)],
            201,
            ['Location' => "/api/v1/reservations/{$reservation->id}/records/{$reservationRecord->id}"]
        );
    }

    /**
     * PUT /api/v1/reservations/{reservation}/records/{id}
     */
    public function update(UpdateReservationRecordRequest $request, Reservation $reservation, ReservationRecord $reservationRecord): JsonResponse
    {
        $this->authorize('update', $reservationRecord);

        $reservation = Reservation::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($reservation->id);
        $reservationRecord = $this->resolveRecord($reservation, $reservationRecord->id);

        $reservationRecord->update($request->validated());

        return response()->json(['data' => new ReservationRecordResource($reservationRecord->fresh())]);
    }

    /**
     * DELETE /api/v1/reservations/{reservation}/records/{id}
     */
    public function destroy(Reservation $reservation, ReservationRecord $reservationRecord): Response
    {
        $this->authorize('delete', $reservationRecord);

        $reservation = Reservation::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($reservation->id);
        $reservationRecord = $this->resolveRecord($reservation, $reservationRecord->id);

        $reservationRecord->delete();

        return response()->noContent();
    }

    private function resolveRecord(Reservation $reservation, int $id): ReservationRecord
    {
        return ReservationRecord::where('reservation_id', $reservation->id)->findOrFail($id);
    }
}
