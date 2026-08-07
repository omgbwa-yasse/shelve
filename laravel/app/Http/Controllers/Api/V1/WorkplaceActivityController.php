<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\WorkplaceActivityResource;
use App\Models\Workplace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * D12 — activité d'un espace de travail (ressource imbriquée, lecture seule).
 *
 * Relevé contre `WorkplaceActivityController` (relu le 2026-08-04). L'accès passe
 * par l'autorisation sur le workplace parent (org-scopé). Aucune écriture exposée.
 */
class WorkplaceActivityController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'workplace_id', 'user_id', 'activity_type', 'subject_id', 'created_at'];
    private const SORTABLE = ['id', 'workplace_id', 'user_id', 'activity_type', 'created_at'];
    private const INCLUDABLE = ['workplace', 'user', 'subject'];

    /**
     * GET /api/v1/workplaces/{workplace}/activities
     */
    public function index(Request $request, Workplace $workplace): JsonResponse
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        $this->authorize('view', $workplace);

        $query = $workplace->activities()->getQuery()->with('user');

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE, 'created_at');
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, WorkplaceActivityResource::class));
    }

    private function workplaceInOrganisation(Workplace $workplace): Workplace
    {
        return Workplace::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($workplace->id);
    }
}
