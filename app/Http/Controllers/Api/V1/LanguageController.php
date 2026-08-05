<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Language\StoreLanguageRequest;
use App\Http\Requests\Api\V1\Language\UpdateLanguageRequest;
use App\Http\Resources\Api\V1\LanguageResource;
use App\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D01 — relu et validé le 2026-08-04 contre le contrôleur Blade et le schéma.
 */
class LanguageController extends Controller
{
    use HandlesApiQueries;

    /**
     * Listes blanches — CONVENTIONS §3. Un champ hors liste provoque un 400, jamais
     * un filtre silencieusement ignoré : un filtre ignoré renvoie des données que
     * l'appelant croit filtrées (risque R03).
     *
     * À RESTREINDRE : la liste ci-dessous reprend toutes les colonnes exploitables.
     */
    private const FILTERABLE = ['id', 'code', 'name', 'native_name'];
    private const SORTABLE = ['id', 'code', 'name', 'native_name'];
    private const INCLUDABLE = [];

    /**
     * GET /api/v1/languages
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Language::class);

        $query = Language::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, LanguageResource::class));
    }

    /**
     * GET /api/v1/languages/{id}
     */
    public function show(Language $language): JsonResponse
    {
        $this->authorize('view', $language);

        return response()->json(['data' => new LanguageResource($language)]);
    }

    /**
     * POST /api/v1/languages
     */
    public function store(StoreLanguageRequest $request): JsonResponse
    {
        $this->authorize('create', Language::class);

        $language = Language::create($request->validated());

        return response()->json(
            ['data' => new LanguageResource($language)],
            201,
            ['Location' => "/api/v1/languages/{$language->id}"]
        );
    }

    /**
     * PATCH /api/v1/languages/{id}
     */
    public function update(UpdateLanguageRequest $request, Language $language): JsonResponse
    {
        $this->authorize('update', $language);

        // TODO concurrence optimiste : vérifier If-Match contre updated_at (CONVENTIONS §7).

        $language->update($request->validated());

        return response()->json(['data' => new LanguageResource($language->fresh())]);
    }

    /**
     * DELETE /api/v1/languages/{id}
     */
    public function destroy(Language $language): Response
    {
        $this->authorize('delete', $language);

        // TODO renvoyer 409 si une contrainte d'intégrité empêche la suppression.
        $language->delete();

        return response()->noContent();
    }

    /**
     * POST /api/v1/languages/{locale}/activate — choix de langue de l'agent.
     *
     * Le `switch` Blade reposait sur la session ; l'API est sans état, la langue est
     * portée par le header `Accept-Language` par requête (CONVENTIONS §1.0.3). Cet
     * endpoint ne fait que valider la locale et la positionner pour la requête
     * courante, pour rester compatible avec l'inventaire (1.0.1).
     */
    public function activate(string $locale): JsonResponse
    {
        if (!in_array($locale, ['en', 'fr'], true)) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Validation', 'status' => 422, 'detail' => 'Locale non supportée.', 'errors' => ['locale' => ['Locale non supportée.']]],
                422
            );
        }

        app()->setLocale($locale);

        return response()->json(['data' => ['locale' => $locale]]);
    }
}
