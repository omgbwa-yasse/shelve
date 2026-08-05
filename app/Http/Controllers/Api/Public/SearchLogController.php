<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Public\StoreSearchLogRequest;
use App\Http\Resources\Api\Public\SearchLogResource;
use App\Models\PublicSearchLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * D15 — journaux de recherche du portail public. Réservé à l'usager connecté
 * (`auth:sanctum`) : un usager ne voit et ne supprime que ses propres journaux.
 */
class SearchLogController extends Controller
{
    use HandlesApiQueries;

    /**
     * GET /api/public/search-logs — historique des recherches de l'usager.
     */
    public function index(Request $request): JsonResponse
    {
        $page = PublicSearchLog::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(min((int) $request->get('per_page', 10), 50))
            ->withQueryString();

        return response()->json($this->paginatedResponse($page, SearchLogResource::class));
    }

    /**
     * POST /api/public/search-logs — enregistre une recherche.
     */
    public function store(StoreSearchLogRequest $request): JsonResponse
    {
        $searchLog = PublicSearchLog::create([
            'user_id' => $request->user()->id,
            'search_term' => $request->validated('search_term'),
            'filters' => $request->validated('filters'),
            'results_count' => $request->validated('results_count', 0),
        ]);

        return response()->json(
            ['data' => new SearchLogResource($searchLog)],
            201,
            ['Location' => "/api/public/search-logs/{$searchLog->id}"]
        );
    }

    /**
     * DELETE /api/public/search-logs — efface tout l'historique de l'usager.
     */
    public function clear(Request $request): JsonResponse
    {
        PublicSearchLog::where('user_id', $request->user()->id)->delete();

        return response()->noContent();
    }

    /**
     * DELETE /api/public/search-logs/{searchLog} — supprime un journal, à
     * condition qu'il appartienne à l'usager connecté (403 sinon).
     */
    public function destroy(Request $request, PublicSearchLog $searchLog): JsonResponse
    {
        abort_unless($searchLog->user_id === $request->user()->id, 403);

        $searchLog->delete();

        return response()->noContent();
    }
}
