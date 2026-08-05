<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CommunicationResource;
use App\Models\Communication;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * D10 — Recherche de communications, porté le 2026-08-05 contre
 * `SearchCommunicationController` (Blade).
 *
 * Les communications sont org-scopées (double organisation émetteur/bénéficiaire,
 * R03) : la requête est bornée à l'organisation courante, sauf super-admin.
 */
class SearchCommunicationController extends Controller
{
    use HandlesApiQueries;

    /**
     * GET /api/v1/search/communications?categ=dates|code|operator|operator-organisation
     *     |user|user-organisation|return-available|not-return|unreturn|return-effective
     *     &id=&value=&date_exact=&date_start=&date_end=
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Communication::class);

        $query = Communication::query();
        $this->scopeOrganisation($query);

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

            case 'operator':
                $query->where('operator_id', $request->input('id'));
                break;

            case 'operator-organisation':
                $query->where('operator_organisation_id', $request->input('id'));
                break;

            case 'user':
                $query->where('user_id', $request->input('id'));
                break;

            case 'user-organisation':
                $query->where('user_organisation_id', $request->input('id'));
                break;

            case 'return-available':
                $query->where('return_date', '>=', now()->format('Y-m-d'));
                break;

            case 'not-return':
                $query->whereNull('return_effective');
                break;

            case 'unreturn':
                $query->whereNull('return_date');
                break;

            case 'return-effective':
                $query->where('return_effective', '<=', now());
                break;
        }

        $page = $query->orderBy('created_at', 'desc')
            ->paginate($this->pageSize($request))
            ->withQueryString();

        return response()->json($this->paginatedResponse($page, CommunicationResource::class));
    }

    /**
     * GET /api/v1/search/communications/advanced?field[]=&operator[]=&value[]=
     */
    public function advanced(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Communication::class);

        $fields = $request->input('field');
        $operators = $request->input('operator');
        $values = $request->input('value');

        $query = Communication::query();
        $this->scopeOrganisation($query);

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
            'operator',
            'operatorOrganisation',
            'user',
            'userOrganisation',
            'records',
        ])
            ->orderBy('created_at', 'desc')
            ->paginate($this->pageSize($request))
            ->withQueryString();

        return response()->json($this->paginatedResponse($page, CommunicationResource::class));
    }

    private function applyCriteria(Builder $query, string $field, string $operator, mixed $value): void
    {
        switch ($field) {
            case 'code':
            case 'name':
            case 'content':
                $this->applyTextSearch($query, $field, $operator, $value);
                break;

            case 'return_date':
            case 'return_effective':
                $this->applyDateSearch($query, $field, $operator, $value);
                break;

            case 'operator':
                $this->applyUserSearch($query, 'operator_id', $operator, $value);
                break;

            case 'user':
                $this->applyUserSearch($query, 'user_id', $operator, $value);
                break;

            case 'operator_organisation':
                $this->applyUserSearch($query, 'operator_organisation_id', $operator, $value);
                break;

            case 'user_organisation':
                $this->applyUserSearch($query, 'user_organisation_id', $operator, $value);
                break;

            case 'status':
                $this->applyStatusSearch($query, $operator, $value);
                break;

            case 'record':
                $query->whereHas(
                    'records',
                    fn (Builder $q) => $q->where('record_id', $value)
                );
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

    private function applyStatusSearch(Builder $query, string $operator, mixed $value): void
    {
        if ($operator === 'avec') {
            $query->where('status', $value);
        } else {
            $query->where(function (Builder $q) use ($value) {
                $q->where('status', '!=', $value)->orWhereNull('status');
            });
        }
    }

    /**
     * Restreint la requête à l'organisation courante, sauf super-admin (R03).
     */
    private function scopeOrganisation(Builder $query): void
    {
        if (!Auth::user()->isSuperAdmin()) {
            $query->forOrganisation(Auth::user()->current_organisation_id);
        }
    }
}
