<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RecordAuthor\StoreRecordAuthorRequest;
use App\Http\Resources\Api\V1\AuthorResource;
use App\Models\Author;
use App\Models\Record;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D02 — auteurs d'une notice, relu et validé le 2026-08-04 contre `RecordAuthorController`.
 *
 * Le Blade `RecordAuthorController` est le référentiel `Author` (déjà porté en D01,
 * ressource globale `authors`). Ici est portée la **pivot** `record_author` (table
 * unifiée créée en phase 4), sous forme de sous-ressource de la notice : les auteurs
 * associés sont listés/associés/détachés sous `/records/{record}/authors`.
 *
 * Org-scopée par héritage de la notice parente (motif D03) : la notice est résolue
 * dans l'organisation courante, sinon 404. La Policy est `AuthorPolicy` (D01,
 * préfixe `author_*`) : l'auteur est un référentiel global, l'association se fait sous
 * ses permissions. Aucun modèle de pivot dédié : la relation `Record::authors()` porte
 * la pivot `record_author` (qui a une colonne `id`).
 */
class RecordAuthorController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'type_id', 'name', 'lifespan', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'type_id', 'name', 'lifespan', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['authorType', 'parent', 'contacts'];

    /**
     * GET /api/v1/records/{record}/authors
     */
    public function index(Record $record, Request $request): JsonResponse
    {
        $this->authorize('viewAny', Author::class);

        $record = Record::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($record->id);

        // Join explicite + `select('authors.*')` : `getQuery()` sur le belongsToMany
        // inclut `record_author.id` dans le SELECT, qui éclipse `authors.id` à
        // l'hydratation (constaté en test : data.id valait l'id de la pivot).
        $query = Author::query()
            ->join('record_author', 'authors.id', '=', 'record_author.author_id')
            ->where('record_author.record_id', $record->id)
            ->select('authors.*');

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE, 'authors.id');
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, AuthorResource::class));
    }

    /**
     * GET /api/v1/records/{record}/authors/{author}
     */
    public function show(Record $record, Author $author): JsonResponse
    {
        $this->authorize('view', $author);

        $record = Record::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($record->id);
        $author = $record->authors()->whereKey($author->id)->firstOrFail();

        return response()->json(['data' => new AuthorResource($author)]);
    }

    /**
     * POST /api/v1/records/{record}/authors
     */
    public function store(StoreRecordAuthorRequest $request, Record $record): JsonResponse
    {
        $this->authorize('create', Author::class);

        $record = Record::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($record->id);

        $record->authors()->syncWithoutDetaching([$request->validated()['author_id']]);

        $author = Author::findOrFail($request->validated()['author_id']);

        return response()->json(
            ['data' => new AuthorResource($author)],
            201,
            ['Location' => "/api/v1/records/{$record->id}/authors/{$author->id}"]
        );
    }

    /**
     * DELETE /api/v1/records/{record}/authors/{author}
     */
    public function destroy(Record $record, Author $author): Response
    {
        $this->authorize('delete', $author);

        $record = Record::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($record->id);

        $record->authors()->detach($author->id);

        return response()->noContent();
    }
}
