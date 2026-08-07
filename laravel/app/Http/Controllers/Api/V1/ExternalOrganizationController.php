<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ExternalOrganization\StoreExternalOrganizationRequest;
use App\Http\Requests\Api\V1\ExternalOrganization\UpdateExternalOrganizationRequest;
use App\Http\Resources\Api\V1\ExternalOrganizationResource;
use App\Models\ExternalOrganization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D01 — relu et validé le 2026-08-04 contre `ExternalOrganizationController` et le schéma.
 *
 * Règle métier conservée du contrôleur Blade : à la suppression, les contacts de
 * l'organisation sont dissociés (`external_organization_id` à NULL), puis
 * l'organisation est supprimée — refus 409 si elle est liée à des courriers.
 */
class ExternalOrganizationController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = [
        'id', 'name', 'legal_form', 'email', 'city', 'country', 'is_verified', 'created_at', 'updated_at',
    ];
    private const SORTABLE = ['id', 'name', 'legal_form', 'city', 'country', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['contacts'];

    /**
     * GET /api/v1/external-organizations
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ExternalOrganization::class);

        $query = ExternalOrganization::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, ExternalOrganizationResource::class));
    }

    /**
     * GET /api/v1/external-organizations/{id}
     */
    public function show(ExternalOrganization $externalOrganization): JsonResponse
    {
        $this->authorize('view', $externalOrganization);

        return response()->json(['data' => new ExternalOrganizationResource($externalOrganization)]);
    }

    /**
     * POST /api/v1/external-organizations
     */
    public function store(StoreExternalOrganizationRequest $request): JsonResponse
    {
        $this->authorize('create', ExternalOrganization::class);

        $organization = ExternalOrganization::create($request->validated());

        return response()->json(
            ['data' => new ExternalOrganizationResource($organization)],
            201,
            ['Location' => "/api/v1/external-organizations/{$organization->id}"]
        );
    }

    /**
     * PATCH /api/v1/external-organizations/{id}
     */
    public function update(UpdateExternalOrganizationRequest $request, ExternalOrganization $externalOrganization): JsonResponse
    {
        $this->authorize('update', $externalOrganization);

        $externalOrganization->update($request->validated());

        return response()->json(['data' => new ExternalOrganizationResource($externalOrganization->fresh())]);
    }

    /**
     * DELETE /api/v1/external-organizations/{id}
     */
    public function destroy(ExternalOrganization $externalOrganization): Response
    {
        $this->authorize('delete', $externalOrganization);

        // Même garde que le contrôleur Blade : 409 si liée à des courriers.
        $mailsCount = $externalOrganization->sentMails()->count() + $externalOrganization->receivedMails()->count();

        if ($mailsCount > 0) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Conflit d\'intégrité', 'status' => 409, 'detail' => 'Impossible de supprimer cette organisation car elle est associée à des courriers.'],
                409
            );
        }

        // Dissocier les contacts, comme le faisait le contrôleur Blade.
        $externalOrganization->contacts()->update(['external_organization_id' => null]);

        $externalOrganization->delete();

        return response()->noContent();
    }
}
