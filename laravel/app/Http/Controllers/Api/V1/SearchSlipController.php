<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SlipResource;
use App\Models\Slip;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * D10 — Recherche de bordereaux, porté le 2026-08-05 contre `SearchSlipController` (Blade).
 *
 * Les bordereaux sont org-scopés (double organisation émetteur `officer_organisation_id`
 * / bénéficiaire `user_organisation_id`, R03) via `Slip::forOrganisation`.
 */
class SearchSlipController extends Controller
{
    use HandlesApiQueries;

    /**
     * GET /api/v1/search/slips/sort?categ=dates|code|officer|officer-organisation|user
     *     |user-organisation|received|approved|integrated|project|draft
     *     &id=&value=&date_exact=&date_start=&date_end=
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Slip::class);

        $query = Slip::forOrganisation(Auth::user()->current_organisation_id);

        switch ($request->input('categ')) {
            case 'dates':
                $exactDate = $request->input('date_exact');
                $startDate = $request->input('date_start');
                $endDate = $request->input('date_end');

                if ($exactDate) {
                    $query->whereDate('created_at', $exactDate);
                }

                if ($startDate && $endDate) {
                    $query->where(function (Builder $q) use ($startDate, $endDate) {
                        $q->whereDate('created_at', '>=', $startDate)
                            ->whereDate('created_at', '<=', $endDate);
                    });
                }
                break;

            case 'code':
                $query->where('code', $request->input('value'));
                break;

            case 'officer':
                $query->where('operator_id', $request->input('id'));
                break;

            case 'officer-organisation':
                $query->where('officer_organisation_id', $request->input('id'));
                break;

            case 'user':
                $query->where('user_id', $request->input('id'));
                break;

            case 'user-organisation':
                $query->where('user_organisation_id', $request->input('id'));
                break;

            case 'received':
                $query->where(['is_received' => true, 'is_integrated' => false]);
                break;

            case 'approved':
                $query->where(['is_approved' => true, 'is_received' => true, 'is_integrated' => false]);
                break;

            case 'integrated':
                $query->where(['is_approved' => true, 'is_received' => true, 'is_integrated' => true]);
                break;

            case 'project':
            case 'draft':
            case 'brouillon':
                $query->whereNotNull('created_at')
                    ->whereNotNull('name')
                    ->whereNotNull('code')
                    ->where('is_approved', false)
                    ->where('is_received', false)
                    ->where('is_integrated', false);
                break;
        }

        $page = $query->orderBy('created_at', 'desc')
            ->paginate($this->pageSize($request))
            ->withQueryString();

        return response()->json($this->paginatedResponse($page, SlipResource::class));
    }

    /**
     * GET /api/v1/search/slips/advanced?field[]=&operator[]=&value[]=
     */
    public function advanced(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Slip::class);

        $fields = $request->input('field');
        $operators = $request->input('operator');
        $values = $request->input('value');

        $query = Slip::forOrganisation(Auth::user()->current_organisation_id);

        if ($fields && $operators && $values) {
            foreach ($fields as $index => $field) {
                $operator = $operators[$index] ?? null;
                $value = $values[$index] ?? null;

                if ($field === '' || $field === null || $operator === '' || $operator === null || $value === '') {
                    continue;
                }

                $this->applyCriteria($query, $field, $operator, $value);
            }
        }

        $page = $query->with([
            'officerOrganisation', 'officer', 'userOrganisation', 'user', 'slipStatus',
            'records.level', 'records.support', 'records.activity', 'records.containers',
            'receivedAgent', 'approvedAgent', 'integratedAgent',
        ])
            ->orderBy('created_at', 'desc')
            ->paginate($this->pageSize($request))
            ->withQueryString();

        return response()->json($this->paginatedResponse($page, SlipResource::class));
    }

    /**
     * GET /api/v1/search/slips/organisation
     */
    public function organisation(): JsonResponse
    {
        $this->authorize('viewAny', Slip::class);

        $page = Slip::forOrganisation(Auth::user()->current_organisation_id)
            ->with([
                'officerOrganisation', 'officer', 'userOrganisation', 'user', 'slipStatus', 'records',
                'receivedAgent', 'approvedAgent', 'integratedAgent',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate($this->pageSize(request()))
            ->withQueryString();

        return response()->json($this->paginatedResponse($page, SlipResource::class));
    }

    private function applyCriteria(Builder $query, string $field, string $operator, mixed $value): void
    {
        switch ($field) {
            case 'code':
            case 'name':
            case 'description':
                $this->applyTextSearch($query, $field, $operator, $value);
                break;

            case 'received_date':
            case 'approved_date':
            case 'integrated_date':
                $this->applyDateSearch($query, $field, $operator, $value);
                break;

            case 'officer':
                $this->applyUserSearch($query, 'officer_id', $operator, $value);
                break;

            case 'user':
                $this->applyUserSearch($query, 'user_id', $operator, $value);
                break;

            case 'officer_organisation':
                $this->applyUserSearch($query, 'officer_organisation_id', $operator, $value);
                break;

            case 'user_organisation':
                $this->applyUserSearch($query, 'user_organisation_id', $operator, $value);
                break;

            case 'slip_status':
                if ($operator === 'avec') {
                    $query->where('slip_status_id', $value);
                } else {
                    $query->where(function (Builder $q) use ($value) {
                        $q->where('slip_status_id', '!=', $value)->orWhereNull('slip_status_id');
                    });
                }
                break;

            case 'received_by':
            case 'approved_by':
            case 'integrated_by':
                $this->applyUserSearch($query, $field, $operator, $value);
                break;

            case 'record':
                if ($operator === 'avec') {
                    $query->whereHas('records', fn (Builder $q) => $q->where('code', 'like', "%$value%")->orWhere('name', 'like', "%$value%"));
                } else {
                    $query->whereDoesntHave('records', fn (Builder $q) => $q->where('code', 'like', "%$value%")->orWhere('name', 'like', "%$value%"));
                }
                break;

            case 'container':
                if ($operator === 'avec') {
                    $query->whereHas('records.containers', fn (Builder $q) => $q->where('containers.id', $value));
                } else {
                    $query->whereDoesntHave('records.containers', fn (Builder $q) => $q->where('containers.id', $value));
                }
                break;
        }
    }

    private function applyTextSearch(Builder $query, string $field, string $operator, mixed $value): void
    {
        if ($operator === 'commence par') {
            $query->where($field, 'like', $value . '%');
        } elseif ($operator === 'ne contient pas') {
            $query->where($field, 'not like', '%' . $value . '%');
        } else {
            $query->where($field, 'like', '%' . $value . '%');
        }
    }

    private function applyDateSearch(Builder $query, string $field, string $operator, mixed $value): void
    {
        $query->whereDate($field, $operator, $value);
    }

    private function applyUserSearch(Builder $query, string $field, string $operator, mixed $value): void
    {
        if ($operator === 'avec') {
            $query->where($field, $value);
        } else {
            $query->where(function (Builder $q) use ($field, $value) {
                $q->where($field, '!=', $value)->orWhereNull($field);
            });
        }
    }
}
