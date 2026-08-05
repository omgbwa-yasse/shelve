<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RecordDigitalFolderMetadataProfile\StoreRecordDigitalFolderMetadataProfileRequest;
use App\Http\Requests\Api\V1\RecordDigitalFolderMetadataProfile\UpdateRecordDigitalFolderMetadataProfileRequest;
use App\Http\Resources\Api\V1\RecordDigitalFolderMetadataProfileResource;
use App\Models\RecordDigitalFolderMetadataProfile;
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
class RecordDigitalFolderMetadataProfileController extends Controller
{
    use HandlesApiQueries;

    /**
     * Listes blanches — CONVENTIONS §3. Un champ hors liste provoque un 400, jamais
     * un filtre silencieusement ignoré : un filtre ignoré renvoie des données que
     * l'appelant croit filtrées (risque R03).
     *
     * À RESTREINDRE : la liste ci-dessous reprend toutes les colonnes exploitables.
     */
    private const FILTERABLE = ['id', 'folder_type_id', 'metadata_definition_id', 'mandatory', 'visible', 'readonly', 'sort_order', 'created_by', 'updated_by', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'folder_type_id', 'metadata_definition_id', 'mandatory', 'visible', 'readonly', 'sort_order', 'created_by', 'updated_by', 'created_at', 'updated_at'];
    private const INCLUDABLE = [];

    /**
     * GET /api/v1/record-digital-folder-metadata-profiles
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', RecordDigitalFolderMetadataProfile::class);

        $query = RecordDigitalFolderMetadataProfile::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, RecordDigitalFolderMetadataProfileResource::class));
    }

    /**
     * GET /api/v1/record-digital-folder-metadata-profiles/{id}
     */
    public function show(RecordDigitalFolderMetadataProfile $folderProfile): JsonResponse
    {
        $this->authorize('view', $folderProfile);

        return response()->json(['data' => new RecordDigitalFolderMetadataProfileResource($folderProfile)]);
    }

    /**
     * POST /api/v1/record-digital-folder-metadata-profiles
     */
    public function store(StoreRecordDigitalFolderMetadataProfileRequest $request): JsonResponse
    {
        $this->authorize('create', RecordDigitalFolderMetadataProfile::class);

        $recordDigitalFolderMetadataProfile = RecordDigitalFolderMetadataProfile::create($request->validated() + ['created_by' => Auth::id()]);

        return response()->json(
            ['data' => new RecordDigitalFolderMetadataProfileResource($recordDigitalFolderMetadataProfile)],
            201,
            ['Location' => "/api/v1/record-digital-folder-metadata-profiles/{$recordDigitalFolderMetadataProfile->id}"]
        );
    }

    /**
     * PATCH /api/v1/record-digital-folder-metadata-profiles/{id}
     */
    public function update(UpdateRecordDigitalFolderMetadataProfileRequest $request, RecordDigitalFolderMetadataProfile $folderProfile): JsonResponse
    {
        $this->authorize('update', $folderProfile);

        // TODO concurrence optimiste : vérifier If-Match contre updated_at (CONVENTIONS §7).

        $folderProfile->update($request->validated());

        return response()->json(['data' => new RecordDigitalFolderMetadataProfileResource($folderProfile->fresh())]);
    }

    /**
     * DELETE /api/v1/record-digital-folder-metadata-profiles/{id}
     */
    public function destroy(RecordDigitalFolderMetadataProfile $folderProfile): Response
    {
        $this->authorize('delete', $folderProfile);

        // TODO renvoyer 409 si une contrainte d'intégrité empêche la suppression.
        $folderProfile->delete();

        return response()->noContent();
    }
}
