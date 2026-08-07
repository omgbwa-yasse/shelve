<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ThesaurusConcept\StoreThesaurusConceptRequest;
use App\Http\Requests\Api\V1\ThesaurusConcept\UpdateThesaurusConceptRequest;
use App\Http\Resources\Api\V1\ThesaurusConceptResource;
use App\Models\ThesaurusConcept;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D08 — concepts de thésaurus (SKOS), référentiel global.
 *
 * Relevé contre `ThesaurusController` (show, concepts, autocomplete, searchApi)
 * et `ThesaurusSearchController` (search). Le CRUD d'un concept n'existe qu'en
 * ébauche côté Blade (update/destroy vides) : les règles sont reprises du modèle
 * et des scopes SKOS. Les actions `search` et `autocomplete` sont portées ici.
 */
class ThesaurusConceptController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'scheme_id', 'uri', 'notation', 'status', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'scheme_id', 'uri', 'notation', 'status', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['scheme', 'labels', 'notes', 'properties', 'broaderConcepts', 'narrowerConcepts', 'relatedConcepts', 'records'];

    /**
     * GET /api/v1/thesaurus-concepts
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ThesaurusConcept::class);

        $query = ThesaurusConcept::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, ThesaurusConceptResource::class));
    }

    /**
     * GET /api/v1/thesaurus-concepts/search
     *
     * Recherche multi-critères reprise de `ThesaurusSearchController::search`.
     * Les requêtes `label_value`/`note_value` du Blade pointaient des colonnes
     * inexistantes : corrigées vers `literal_form` (ThesaurusLabel) et `content`
     * (ThesaurusConceptNote).
     */
    public function search(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ThesaurusConcept::class);

        $query = ThesaurusConcept::with(['labels', 'scheme', 'notes']);

        if ($request->filled('query')) {
            $query->whereHas('labels', fn ($q) => $q->where('literal_form', 'LIKE', '%' . $request->input('query') . '%'));
        }

        if ($request->filled('language')) {
            $query->whereHas('labels', fn ($q) => $q->where('language', $request->input('language')));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category')) {
            $query->whereHas('scheme', fn ($q) => $q->where('title', 'LIKE', '%' . $request->input('category') . '%'));
        }

        if ($request->filled('content_search')) {
            $query->whereHas('notes', fn ($q) => $q->where('content', 'LIKE', '%' . $request->input('content_search') . '%'));
        }

        if ($request->filled('has_narrower')) {
            $query->whereHas('narrowerConcepts');
        }

        if ($request->filled('has_broader')) {
            $query->whereHas('broaderConcepts');
        }

        if ($request->filled('has_related')) {
            $query->whereHas('relatedConcepts');
        }

        if ($request->filled('is_top_term')) {
            $query->whereDoesntHave('broaderConcepts');
        }

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, ThesaurusConceptResource::class));
    }

    /**
     * GET /api/v1/thesaurus-concepts/autocomplete
     *
     * Autocomplétion reprise de `ThesaurusController::autocomplete`.
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ThesaurusConcept::class);

        $request->validate([
            'q' => 'required|string|min:2',
            'scheme_id' => 'nullable|exists:thesaurus_schemes,id',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        $query = ThesaurusConcept::with(['labels', 'scheme'])
            ->whereHas('labels', fn ($q) => $q->where('literal_form', 'LIKE', '%' . $request->input('q') . '%'));

        if ($request->filled('scheme_id')) {
            $query->where('scheme_id', $request->input('scheme_id'));
        }

        $concepts = $query->limit($request->integer('limit', 20))->get();

        return response()->json([
            'data' => $concepts->map(fn (ThesaurusConcept $concept) => [
                'id' => $concept->id,
                'text' => $concept->preferred_label,
                'scheme' => $concept->scheme ? $concept->scheme->title : null,
            ]),
        ]);
    }

    /**
     * GET /api/v1/thesaurus-concepts/{id}
     */
    public function show(ThesaurusConcept $thesaurusConcept): JsonResponse
    {
        $this->authorize('view', $thesaurusConcept);

        $thesaurusConcept->load([
            'labels',
            'notes',
            'scheme',
            'broaderConcepts.labels',
            'narrowerConcepts.labels',
            'relatedConcepts.labels',
            'records',
        ]);

        return response()->json(['data' => new ThesaurusConceptResource($thesaurusConcept)]);
    }

    /**
     * POST /api/v1/thesaurus-concepts
     */
    public function store(StoreThesaurusConceptRequest $request): JsonResponse
    {
        $this->authorize('create', ThesaurusConcept::class);

        $thesaurusConcept = ThesaurusConcept::create($request->validated());

        return response()->json(
            ['data' => new ThesaurusConceptResource($thesaurusConcept)],
            201,
            ['Location' => "/api/v1/thesaurus-concepts/{$thesaurusConcept->id}"]
        );
    }

    /**
     * PATCH /api/v1/thesaurus-concepts/{id}
     */
    public function update(UpdateThesaurusConceptRequest $request, ThesaurusConcept $thesaurusConcept): JsonResponse
    {
        $this->authorize('update', $thesaurusConcept);

        $thesaurusConcept->update($request->validated());

        return response()->json(['data' => new ThesaurusConceptResource($thesaurusConcept->fresh())]);
    }

    /**
     * DELETE /api/v1/thesaurus-concepts/{id}
     */
    public function destroy(ThesaurusConcept $thesaurusConcept): Response
    {
        $this->authorize('delete', $thesaurusConcept);

        $thesaurusConcept->delete();

        return response()->noContent();
    }
}
