<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RecordSupport\StoreRecordSupportRequest;
use App\Http\Requests\Api\V1\RecordSupport\UpdateRecordSupportRequest;
use App\Http\Resources\Api\V1\RecordSupportResource;
use App\Models\RecordSupport;
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
class RecordSupportController extends Controller
{
    use HandlesApiQueries;

    /**
     * Listes blanches — CONVENTIONS §3. Un champ hors liste provoque un 400, jamais
     * un filtre silencieusement ignoré : un filtre ignoré renvoie des données que
     * l'appelant croit filtrées (risque R03).
     *
     * À RESTREINDRE : la liste ci-dessous reprend toutes les colonnes exploitables.
     */
    private const FILTERABLE = ['id', 'name', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'name', 'created_at', 'updated_at'];
    private const INCLUDABLE = [];

    /**
     * GET /api/v1/record-supports
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', RecordSupport::class);

        $query = RecordSupport::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, RecordSupportResource::class));
    }

    /**
     * GET /api/v1/record-supports/{id}
     */
    public function show(RecordSupport $recordSupport): JsonResponse
    {
        $this->authorize('view', $recordSupport);

        return response()->json(['data' => new RecordSupportResource($recordSupport)]);
    }

    /**
     * POST /api/v1/record-supports
     */
    public function store(StoreRecordSupportRequest $request): JsonResponse
    {
        $this->authorize('create', RecordSupport::class);

        $recordSupport = RecordSupport::create($request->validated());

        return response()->json(
            ['data' => new RecordSupportResource($recordSupport)],
            201,
            ['Location' => "/api/v1/record-supports/{$recordSupport->id}"]
        );
    }

    /**
     * PATCH /api/v1/record-supports/{id}
     */
    public function update(UpdateRecordSupportRequest $request, RecordSupport $recordSupport): JsonResponse
    {
        $this->authorize('update', $recordSupport);

        // TODO concurrence optimiste : vérifier If-Match contre updated_at (CONVENTIONS §7).

        $recordSupport->update($request->validated());

        return response()->json(['data' => new RecordSupportResource($recordSupport->fresh())]);
    }

    /**
     * DELETE /api/v1/record-supports/{id}
     */
    public function destroy(RecordSupport $recordSupport): Response
    {
        $this->authorize('delete', $recordSupport);

        // TODO renvoyer 409 si une contrainte d'intégrité empêche la suppression.
        $recordSupport->delete();

        return response()->noContent();
    }
}
