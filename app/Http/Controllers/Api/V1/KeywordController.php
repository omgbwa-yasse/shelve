<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Keyword\StoreKeywordRequest;
use App\Http\Requests\Api\V1\Keyword\UpdateKeywordRequest;
use App\Http\Resources\Api\V1\KeywordResource;
use App\Models\Keyword;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D01 — relu et validé le 2026-08-04 contre le contrôleur Blade et le schéma.
 */
class KeywordController extends Controller
{
    use HandlesApiQueries;

    /**
     * Listes blanches — CONVENTIONS §3. Un champ hors liste provoque un 400, jamais
     * un filtre silencieusement ignoré : un filtre ignoré renvoie des données que
     * l'appelant croit filtrées (risque R03).
     *
     * À RESTREINDRE : la liste ci-dessous reprend toutes les colonnes exploitables.
     */
    private const FILTERABLE = ['id', 'name', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'name', 'created_at', 'updated_at'];
    private const INCLUDABLE = [];

    /**
     * GET /api/v1/keywords
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Keyword::class);

        $query = Keyword::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, KeywordResource::class));
    }

    /**
     * GET /api/v1/keywords/{id}
     */
    public function show(Keyword $keyword): JsonResponse
    {
        $this->authorize('view', $keyword);

        return response()->json(['data' => new KeywordResource($keyword)]);
    }

    /**
     * POST /api/v1/keywords
     */
    public function store(StoreKeywordRequest $request): JsonResponse
    {
        $this->authorize('create', Keyword::class);

        $keyword = Keyword::create($request->validated());

        return response()->json(
            ['data' => new KeywordResource($keyword)],
            201,
            ['Location' => "/api/v1/keywords/{$keyword->id}"]
        );
    }

    /**
     * PATCH /api/v1/keywords/{id}
     */
    public function update(UpdateKeywordRequest $request, Keyword $keyword): JsonResponse
    {
        $this->authorize('update', $keyword);

        // TODO concurrence optimiste : vérifier If-Match contre updated_at (CONVENTIONS §7).

        $keyword->update($request->validated());

        return response()->json(['data' => new KeywordResource($keyword->fresh())]);
    }

    /**
     * DELETE /api/v1/keywords/{id}
     */
    public function destroy(Keyword $keyword): Response
    {
        $this->authorize('delete', $keyword);

        // Même garde que le contrôleur Blade : un mot-clé utilisé ne se supprime pas.
        $recordsCount = $keyword->records()->count();
        $slipRecordsCount = $keyword->slipRecords()->count();

        if ($recordsCount > 0 || $slipRecordsCount > 0) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Conflit d\'intégrité', 'status' => 409, 'detail' => 'Ce mot-clé est utilisé par ' . ($recordsCount + $slipRecordsCount) . ' enregistrement(s) et ne peut pas être supprimé.'],
                409
            );
        }

        $keyword->delete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/keywords/search?q= — autocomplétion (reprend `KeywordController::search`).
     * Moins de 2 caractères renvoie une liste vide.
     */
    public function search(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Keyword::class);

        $query = trim((string) $request->input('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json(['data' => []]);
        }

        $names = Keyword::where('name', 'LIKE', "%{$query}%")
            ->limit(10)
            ->orderBy('name')
            ->pluck('name');

        return response()->json(['data' => $names]);
    }

    /**
     * POST /api/v1/keywords/process — traite une chaîne de mots-clés et retourne les
     * IDs des mots-clés trouvés ou créés (reprend `KeywordController::processKeywords`).
     */
    public function processKeywords(Request $request): JsonResponse
    {
        $this->authorize('create', Keyword::class);

        $request->validate(['keywords' => 'required|string']);

        $keywords = Keyword::processKeywordsString($request->input('keywords'));

        return response()->json([
            'data' => $keywords->map(fn ($keyword) => ['id' => $keyword->id, 'name' => $keyword->name]),
        ]);
    }
}
