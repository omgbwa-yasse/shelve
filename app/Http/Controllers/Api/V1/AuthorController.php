<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Author\StoreAuthorRequest;
use App\Http\Requests\Api\V1\Author\UpdateAuthorRequest;
use App\Http\Resources\Api\V1\AuthorResource;
use App\Models\Author;
use App\Models\AuthorType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D01 — relu et validé le 2026-08-04 contre `AuthorController` et le schéma.
 *
 * Côté Blade, seuls les endpoints JSON d'aide existaient (`indexApi`, `storeApi`,
 * `authorTypesApi`). L'API v1 expose le CRUD complet : les méthodes `show`, `update`
 * et `destroy` existaient dans le contrôleur Blade sans route associée, elles sont
 * ici réutilisées à l'identique.
 */
class AuthorController extends Controller
{
    use HandlesApiQueries;

    /**
     * Listes blanches — CONVENTIONS §3. Un champ hors liste provoque un 400, jamais
     * un filtre silencieusement ignoré (risque R03).
     */
    private const FILTERABLE = ['id', 'type_id', 'name', 'parent_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'type_id', 'name', 'parent_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['authorType', 'parent', 'contacts'];

    /**
     * GET /api/v1/authors
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Author::class);

        $query = Author::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, AuthorResource::class));
    }

    /**
     * GET /api/v1/authors/{id}
     */
    public function show(Author $author): JsonResponse
    {
        $this->authorize('view', $author);

        return response()->json(['data' => new AuthorResource($author)]);
    }

    /**
     * POST /api/v1/authors
     */
    public function store(StoreAuthorRequest $request): JsonResponse
    {
        $this->authorize('create', Author::class);

        $author = Author::create($request->validated());

        return response()->json(
            ['data' => new AuthorResource($author)],
            201,
            ['Location' => "/api/v1/authors/{$author->id}"]
        );
    }

    /**
     * PATCH /api/v1/authors/{id}
     */
    public function update(UpdateAuthorRequest $request, Author $author): JsonResponse
    {
        $this->authorize('update', $author);

        $author->update($request->validated());

        return response()->json(['data' => new AuthorResource($author->fresh())]);
    }

    /**
     * DELETE /api/v1/authors/{id}
     */
    public function destroy(Author $author): Response
    {
        $this->authorize('delete', $author);

        $author->delete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/author-types — liste des types d'auteurs pour les listes déroulantes.
     * Reprend `AuthorController::authorTypesApi()`.
     */
    public function authorTypes(): JsonResponse
    {
        $this->authorize('viewAny', Author::class);

        return response()->json([
            'data' => AuthorType::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
