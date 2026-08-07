<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ThesaurusScheme\StoreThesaurusSchemeRequest;
use App\Http\Requests\Api\V1\ThesaurusScheme\UpdateThesaurusSchemeRequest;
use App\Http\Resources\Api\V1\ThesaurusSchemeResource;
use App\Models\ThesaurusNamespace;
use App\Models\ThesaurusScheme;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * D08 — schémas de thésaurus (SKOS), référentiel global.
 *
 * Relevé contre `ThesaurusSchemeController` et `ThesaurusController::store`
 * (relu le 2026-08-04). `uri` est générée côté serveur à partir de `identifier`
 * et du `app.url` ; un namespace optionnel est créé/mis à jour avec le schéma.
 */
class ThesaurusSchemeController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'uri', 'identifier', 'title', 'language', 'namespace_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'uri', 'identifier', 'title', 'language', 'namespace_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['namespace', 'concepts', 'topConcepts', 'organizations', 'collections'];

    /**
     * GET /api/v1/thesaurus-schemes
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ThesaurusScheme::class);

        $query = ThesaurusScheme::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE, 'created_at');
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, ThesaurusSchemeResource::class));
    }

    /**
     * GET /api/v1/thesaurus-schemes/{id}
     */
    public function show(ThesaurusScheme $thesaurusScheme): JsonResponse
    {
        $this->authorize('view', $thesaurusScheme);

        return response()->json(['data' => new ThesaurusSchemeResource($thesaurusScheme)]);
    }

    /**
     * POST /api/v1/thesaurus-schemes
     */
    public function store(StoreThesaurusSchemeRequest $request): JsonResponse
    {
        $this->authorize('create', ThesaurusScheme::class);

        $data = $request->validated();

        DB::beginTransaction();
        try {
            // Comme en Blade : URI générée côté serveur, jamais acceptée du client.
            $uri = config('app.url') . '/thesaurus/schemes/' . Str::slug($data['identifier']);

            $scheme = ThesaurusScheme::create([
                'identifier' => $data['identifier'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'language' => $data['language'],
                'uri' => $uri,
            ]);

            if (!empty($data['namespace_uri'])) {
                $namespace = ThesaurusNamespace::create([
                    'prefix' => $data['identifier'],
                    'namespace_uri' => $data['namespace_uri'],
                    'description' => 'Namespace for ' . $data['title'],
                ]);
                $scheme->update(['namespace_id' => $namespace->id]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du schéma de thésaurus: ' . $e->getMessage());
            throw $e;
        }

        return response()->json(
            ['data' => new ThesaurusSchemeResource($scheme->fresh())],
            201,
            ['Location' => "/api/v1/thesaurus-schemes/{$scheme->id}"]
        );
    }

    /**
     * PATCH /api/v1/thesaurus-schemes/{id}
     */
    public function update(UpdateThesaurusSchemeRequest $request, ThesaurusScheme $thesaurusScheme): JsonResponse
    {
        $this->authorize('update', $thesaurusScheme);

        $data = $request->validated();

        DB::beginTransaction();
        try {
            $thesaurusScheme->update([
                'identifier' => $data['identifier'] ?? $thesaurusScheme->identifier,
                'title' => $data['title'] ?? $thesaurusScheme->title,
                'description' => array_key_exists('description', $data) ? $data['description'] : $thesaurusScheme->description,
                'language' => $data['language'] ?? $thesaurusScheme->language,
                'uri' => $data['uri'] ?? $thesaurusScheme->uri,
            ]);

            if (!empty($data['namespace_uri'])) {
                if ($thesaurusScheme->namespace) {
                    $thesaurusScheme->namespace->update([
                        'prefix' => $data['identifier'],
                        'namespace_uri' => $data['namespace_uri'],
                        'description' => 'Namespace for ' . $data['title'],
                    ]);
                } else {
                    $namespace = ThesaurusNamespace::create([
                        'prefix' => $data['identifier'],
                        'namespace_uri' => $data['namespace_uri'],
                        'description' => 'Namespace for ' . $data['title'],
                    ]);
                    $thesaurusScheme->update(['namespace_id' => $namespace->id]);
                }
            } elseif ($thesaurusScheme->namespace_id && empty($data['namespace_uri'])) {
                $thesaurusScheme->update(['namespace_id' => null]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erreur lors de la modification du schéma de thésaurus: ' . $e->getMessage());
            throw $e;
        }

        return response()->json(['data' => new ThesaurusSchemeResource($thesaurusScheme->fresh())]);
    }

    /**
     * DELETE /api/v1/thesaurus-schemes/{id}
     */
    public function destroy(ThesaurusScheme $thesaurusScheme): Response
    {
        $this->authorize('delete', $thesaurusScheme);

        DB::beginTransaction();
        try {
            if ($thesaurusScheme->namespace && $thesaurusScheme->namespace->schemes()->count() <= 1) {
                $thesaurusScheme->namespace->delete();
            }

            $thesaurusScheme->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du schéma de thésaurus: ' . $e->getMessage());
            throw $e;
        }

        return response()->noContent();
    }
}
