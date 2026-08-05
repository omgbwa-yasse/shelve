<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\DollyResource;
use App\Models\Dolly;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * D10 — Recherche de chariots, porté le 2026-08-05 contre `SearchdollyController` (Blade).
 *
 * Chariots org-scopés (`owner_organisation_id`, R03).
 */
class SearchDollyController extends Controller
{
    use HandlesApiQueries;

    /**
     * GET /api/v1/search/dollies?categ=record|communication|transferring|building|shelf
     *     |slip|mail|room|slip_record|container|digital_folder|digital_document|book|book_series
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Dolly::class);

        $query = Dolly::inOrganisation(Auth::user()->current_organisation_id);

        if ($request->filled('categ')) {
            $query->where('category', $request->input('categ'));
        }

        $page = $query->orderBy('created_at', 'desc')
            ->paginate($this->pageSize($request))
            ->withQueryString();

        return response()->json($this->paginatedResponse($page, DollyResource::class));
    }
}
