<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MetadataDefinition\StoreMetadataDefinitionRequest;
use App\Http\Requests\Api\V1\MetadataDefinition\UpdateMetadataDefinitionRequest;
use App\Http\Resources\Api\V1\MetadataDefinitionResource;
use App\Models\MetadataDefinition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * @generated par `php artisan make:api-resource-set` — domaine D02.
 *
 * CE FICHIER EST UN POINT DE DÉPART, PAS UN LIVRABLE.
 * Les règles ci-dessous sont déduites du schéma et des règles déjà présentes dans le
 * contrôleur Blade. Le schéma ne connaît ni les règles métier ni ce que la vue imposait
 * implicitement (risques R01 et R02) : relire le contrôleur ET ses vues avant de valider.
 *
 * Retirer ce bandeau une fois le fichier relu.
 */
class MetadataDefinitionController extends Controller
{
    use HandlesApiQueries;

    /**
     * Listes blanches — CONVENTIONS §3. Un champ hors liste provoque un 400, jamais
     * un filtre silencieusement ignoré : un filtre ignoré renvoie des données que
     * l'appelant croit filtrées (risque R03).
     *
     * À RESTREINDRE : la liste ci-dessous reprend toutes les colonnes exploitables.
     */
    private const FILTERABLE = ['id', 'name', 'code', 'data_type', 'reference_list_id', 'searchable', 'active', 'is_system', 'sort_order', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at'];
    private const SORTABLE = ['id', 'name', 'code', 'data_type', 'reference_list_id', 'searchable', 'active', 'is_system', 'sort_order', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at'];
    private const INCLUDABLE = [];

    /**
     * GET /api/v1/metadata-definitions
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', MetadataDefinition::class);

        $query = MetadataDefinition::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, MetadataDefinitionResource::class));
    }

    /**
     * GET /api/v1/metadata-definitions/{id}
     */
    public function show(MetadataDefinition $metadataDefinition): JsonResponse
    {
        $this->authorize('view', $metadataDefinition);

        return response()->json(['data' => new MetadataDefinitionResource($metadataDefinition)]);
    }

    /**
     * POST /api/v1/metadata-definitions
     */
    public function store(StoreMetadataDefinitionRequest $request): JsonResponse
    {
        $this->authorize('create', MetadataDefinition::class);

        $metadataDefinition = MetadataDefinition::create($request->validated() + ['created_by' => Auth::id()]);

        return response()->json(
            ['data' => new MetadataDefinitionResource($metadataDefinition)],
            201,
            ['Location' => "/api/v1/metadata-definitions/{$metadataDefinition->id}"]
        );
    }

    /**
     * PATCH /api/v1/metadata-definitions/{id}
     */
    public function update(UpdateMetadataDefinitionRequest $request, MetadataDefinition $metadataDefinition): JsonResponse
    {
        $this->authorize('update', $metadataDefinition);

        // TODO concurrence optimiste : vérifier If-Match contre updated_at (CONVENTIONS §7).

        $metadataDefinition->update($request->validated());

        return response()->json(['data' => new MetadataDefinitionResource($metadataDefinition->fresh())]);
    }

    /**
     * DELETE /api/v1/metadata-definitions/{id}
     */
    public function destroy(MetadataDefinition $metadataDefinition): Response
    {
        $this->authorize('delete', $metadataDefinition);

        // TODO renvoyer 409 si une contrainte d'intégrité empêche la suppression.
        $metadataDefinition->delete();

        return response()->noContent();
    }
}
