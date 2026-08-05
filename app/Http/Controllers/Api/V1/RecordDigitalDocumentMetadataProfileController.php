<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RecordDigitalDocumentMetadataProfile\StoreRecordDigitalDocumentMetadataProfileRequest;
use App\Http\Requests\Api\V1\RecordDigitalDocumentMetadataProfile\UpdateRecordDigitalDocumentMetadataProfileRequest;
use App\Http\Resources\Api\V1\RecordDigitalDocumentMetadataProfileResource;
use App\Models\RecordDigitalDocumentMetadataProfile;
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
class RecordDigitalDocumentMetadataProfileController extends Controller
{
    use HandlesApiQueries;

    /**
     * Listes blanches — CONVENTIONS §3. Un champ hors liste provoque un 400, jamais
     * un filtre silencieusement ignoré : un filtre ignoré renvoie des données que
     * l'appelant croit filtrées (risque R03).
     *
     * À RESTREINDRE : la liste ci-dessous reprend toutes les colonnes exploitables.
     */
    private const FILTERABLE = ['id', 'document_type_id', 'metadata_definition_id', 'mandatory', 'visible', 'readonly', 'sort_order', 'created_by', 'updated_by', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'document_type_id', 'metadata_definition_id', 'mandatory', 'visible', 'readonly', 'sort_order', 'created_by', 'updated_by', 'created_at', 'updated_at'];
    private const INCLUDABLE = [];

    /**
     * GET /api/v1/record-digital-document-metadata-profiles
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', RecordDigitalDocumentMetadataProfile::class);

        $query = RecordDigitalDocumentMetadataProfile::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, RecordDigitalDocumentMetadataProfileResource::class));
    }

    /**
     * GET /api/v1/record-digital-document-metadata-profiles/{id}
     */
    public function show(RecordDigitalDocumentMetadataProfile $documentProfile): JsonResponse
    {
        $this->authorize('view', $documentProfile);

        return response()->json(['data' => new RecordDigitalDocumentMetadataProfileResource($documentProfile)]);
    }

    /**
     * POST /api/v1/record-digital-document-metadata-profiles
     */
    public function store(StoreRecordDigitalDocumentMetadataProfileRequest $request): JsonResponse
    {
        $this->authorize('create', RecordDigitalDocumentMetadataProfile::class);

        $recordDigitalDocumentMetadataProfile = RecordDigitalDocumentMetadataProfile::create($request->validated() + ['created_by' => Auth::id()]);

        return response()->json(
            ['data' => new RecordDigitalDocumentMetadataProfileResource($recordDigitalDocumentMetadataProfile)],
            201,
            ['Location' => "/api/v1/record-digital-document-metadata-profiles/{$recordDigitalDocumentMetadataProfile->id}"]
        );
    }

    /**
     * PATCH /api/v1/record-digital-document-metadata-profiles/{id}
     */
    public function update(UpdateRecordDigitalDocumentMetadataProfileRequest $request, RecordDigitalDocumentMetadataProfile $documentProfile): JsonResponse
    {
        $this->authorize('update', $documentProfile);

        // TODO concurrence optimiste : vérifier If-Match contre updated_at (CONVENTIONS §7).

        $documentProfile->update($request->validated());

        return response()->json(['data' => new RecordDigitalDocumentMetadataProfileResource($documentProfile->fresh())]);
    }

    /**
     * DELETE /api/v1/record-digital-document-metadata-profiles/{id}
     */
    public function destroy(RecordDigitalDocumentMetadataProfile $documentProfile): Response
    {
        $this->authorize('delete', $documentProfile);

        // TODO renvoyer 409 si une contrainte d'intégrité empêche la suppression.
        $documentProfile->delete();

        return response()->noContent();
    }
}
