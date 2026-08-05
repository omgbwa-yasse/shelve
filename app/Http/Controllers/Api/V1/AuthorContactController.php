<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AuthorContact\StoreAuthorContactRequest;
use App\Http\Requests\Api\V1\AuthorContact\UpdateAuthorContactRequest;
use App\Http\Resources\Api\V1\AuthorContactResource;
use App\Models\AuthorContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D01 — relu et validé le 2026-08-04 contre `AuthorContactController` et le schéma.
 *
 * Le contrôleur Blade ne validait rien (création par `$request->all()`), la
 * validation est reconstituée du schéma (risque R01).
 */
class AuthorContactController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'author_id', 'email', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'author_id', 'email', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['author'];

    /**
     * GET /api/v1/author-contacts
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AuthorContact::class);

        $query = AuthorContact::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, AuthorContactResource::class));
    }

    /**
     * GET /api/v1/author-contacts/{id}
     */
    public function show(AuthorContact $authorContact): JsonResponse
    {
        $this->authorize('view', $authorContact);

        return response()->json(['data' => new AuthorContactResource($authorContact)]);
    }

    /**
     * POST /api/v1/author-contacts
     */
    public function store(StoreAuthorContactRequest $request): JsonResponse
    {
        $this->authorize('create', AuthorContact::class);

        $contact = AuthorContact::create($request->validated());

        return response()->json(
            ['data' => new AuthorContactResource($contact)],
            201,
            ['Location' => "/api/v1/author-contacts/{$contact->id}"]
        );
    }

    /**
     * PATCH /api/v1/author-contacts/{id}
     */
    public function update(UpdateAuthorContactRequest $request, AuthorContact $authorContact): JsonResponse
    {
        $this->authorize('update', $authorContact);

        $authorContact->update($request->validated());

        return response()->json(['data' => new AuthorContactResource($authorContact->fresh())]);
    }

    /**
     * DELETE /api/v1/author-contacts/{id}
     */
    public function destroy(AuthorContact $authorContact): Response
    {
        $this->authorize('delete', $authorContact);

        $authorContact->delete();

        return response()->noContent();
    }
}
