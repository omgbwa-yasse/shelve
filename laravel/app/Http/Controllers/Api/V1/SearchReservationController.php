<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ReservationResource;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * D10 — Recherche de réservations, porté le 2026-08-05 contre
 * `SearchReservationController` (Blade).
 *
 * Réservations org-scopées (double organisation émetteur/bénéficiaire, R03) via
 * `Reservation::inOrganisation`.
 */
class SearchReservationController extends Controller
{
    use HandlesApiQueries;

    /**
     * GET /api/v1/search/reservations?categ=dates|code|operator|operator-organisation
     *     |user|user-organisation|return-available|not-return|unreturn|return-effective
     *     |approved|InProgress&id=&value=&date_exact=&date_start=&date_end=
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Reservation::class);

        $query = Reservation::inOrganisation(Auth::user()->current_organisation_id);

        switch ($request->input('categ')) {
            case 'dates':
                $exactDate = $request->input('date_exact');
                $startDate = $request->input('date_start');
                $endDate = $request->input('date_end');

                if ($exactDate) {
                    $query->whereDate('created_at', $exactDate);
                } elseif ($startDate && $endDate) {
                    $query->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);
                }
                break;

            case 'code':
                if ($request->filled('value')) {
                    $query->where('code', $request->input('value'));
                }
                break;

            case 'operator':
                if ($request->filled('id')) {
                    $query->where('operator_id', $request->input('id'));
                }
                break;

            case 'operator-organisation':
                if ($request->filled('id')) {
                    $query->where('operator_organisation_id', $request->input('id'));
                }
                break;

            case 'user':
                if ($request->filled('id')) {
                    $query->where('user_id', $request->input('id'));
                }
                break;

            case 'user-organisation':
                if ($request->filled('id')) {
                    $query->where('user_organisation_id', $request->input('id'));
                }
                break;

            case 'return-available':
                $query->where('return_date', '<=', now()->format('Y-m-d'))
                    ->whereNull('return_effective');
                break;

            case 'not-return':
                $query->where('return_date', '<=', now()->format('Y-m-d'));
                break;

            case 'unreturn':
                $query->whereNull('return_date');
                break;

            case 'return-effective':
                $query->where('return_effective', '<=', now()->format('Y-m-d'));
                break;

            case 'approved':
                $query->where('status', ReservationStatus::APPROVED);
                break;

            case 'InProgress':
                $query->where('status', ReservationStatus::PENDING);
                break;
        }

        $page = $query->with(['operator', 'operatorOrganisation', 'user', 'userOrganisation', 'records', 'communication'])
            ->orderBy('created_at', 'desc')
            ->paginate($this->pageSize($request))
            ->withQueryString();

        return response()->json($this->paginatedResponse($page, ReservationResource::class));
    }
}
