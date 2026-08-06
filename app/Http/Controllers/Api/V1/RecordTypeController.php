<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RecordType\StoreRecordTypeRequest;
use App\Http\Requests\Api\V1\RecordType\UpdateRecordTypeRequest;
use App\Http\Resources\Api\V1\RecordTypeResource;
use App\Models\RecordType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
class RecordTypeController extends Controller
{
    use HandlesApiQueries;

    /**
     * Listes blanches — CONVENTIONS §3. Un champ hors liste provoque un 400, jamais
     * un filtre silencieusement ignoré : un filtre ignoré renvoie des données que
     * l'appelant croit filtrées (risque R03).
     *
     * À RESTREINDRE : la liste ci-dessous reprend toutes les colonnes exploitables.
     */
    private const FILTERABLE = ['id', 'code', 'name', 'parent_id', 'reference_list_id', 'is_container', 'icon', 'color', 'code_prefix', 'code_pattern', 'max_file_size', 'requires_versioning', 'requires_approval', 'requires_signature', 'default_access_level', 'is_active', 'display_order', 'created_by', 'updated_by', 'legacy_type', 'created_at', 'updated_at', 'deleted_at'];
    private const SORTABLE = ['id', 'code', 'name', 'parent_id', 'reference_list_id', 'is_container', 'icon', 'color', 'code_prefix', 'code_pattern', 'max_file_size', 'requires_versioning', 'requires_approval', 'requires_signature', 'default_access_level', 'is_active', 'display_order', 'created_by', 'updated_by', 'legacy_type', 'created_at', 'updated_at', 'deleted_at'];
    private const INCLUDABLE = [];

    /**
     * GET /api/v1/record-types
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', RecordType::class);

        $query = RecordType::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, RecordTypeResource::class));
    }

    /**
     * GET /api/v1/record-types/{id}
     */
    public function show(RecordType $recordType): JsonResponse
    {
        $this->authorize('view', $recordType);

        return response()->json(['data' => new RecordTypeResource($recordType)]);
    }

    /**
     * POST /api/v1/record-types
     */
    public function store(StoreRecordTypeRequest $request): JsonResponse
    {
        $this->authorize('create', RecordType::class);

        $recordType = RecordType::create($request->validated());

        return response()->json(
            ['data' => new RecordTypeResource($recordType)],
            201,
            ['Location' => "/api/v1/record-types/{$recordType->id}"]
        );
    }

    /**
     * PATCH /api/v1/record-types/{id}
     */
    public function update(UpdateRecordTypeRequest $request, RecordType $recordType): JsonResponse
    {
        $this->authorize('update', $recordType);

        // TODO concurrence optimiste : vérifier If-Match contre updated_at (CONVENTIONS §7).

        $recordType->update($request->validated());

        return response()->json(['data' => new RecordTypeResource($recordType->fresh())]);
    }

    /**
     * DELETE /api/v1/record-types/{id}
     */
    public function destroy(RecordType $recordType): Response
    {
        $this->authorize('delete', $recordType);

        // TODO renvoyer 409 si une contrainte d'intégrité empêche la suppression.
        $recordType->delete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/record-types/{recordType}/metadata-fields — schéma des métadonnées
     * visibles pour ce type (sans valeur : sert à construire le formulaire de
     * création d'une notice avant qu'elle n'existe). Voir `Record::getVisibleMetadataFields()`
     * pour l'équivalent "avec valeurs" côté notice existante.
     */
    public function metadataFields(RecordType $recordType): JsonResponse
    {
        $this->authorize('view', $recordType);

        $fields = $recordType->getVisibleMetadataDefinitions()->map(fn ($definition) => [
            'code' => $definition->code,
            'name' => $definition->name,
            'data_type' => $definition->data_type,
            'options' => $definition->options,
            'value' => $definition->pivot->default_value,
            'required' => (bool) $definition->pivot->mandatory,
            'readonly' => (bool) $definition->pivot->readonly,
            'group' => $definition->pivot->group,
        ])->values();

        return response()->json(['data' => $fields]);
    }
}
