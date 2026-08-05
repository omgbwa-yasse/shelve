<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ExternalContact\StoreExternalContactRequest;
use App\Http\Requests\Api\V1\ExternalContact\UpdateExternalContactRequest;
use App\Http\Resources\Api\V1\ExternalContactResource;
use App\Models\ExternalContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D01 — relu et validé le 2026-08-04 contre `ExternalContactController` et le schéma.
 *
 * Règle métier conservée du contrôleur Blade : un contact déclaré « principal »
 * retire ce statut aux autres contacts de la même organisation externe.
 */
class ExternalContactController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = [
        'id', 'first_name', 'last_name', 'email', 'phone', 'position',
        'external_organization_id', 'is_primary_contact', 'created_at', 'updated_at',
    ];
    private const SORTABLE = ['id', 'first_name', 'last_name', 'email', 'position', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['organization'];

    /**
     * GET /api/v1/external-contacts
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ExternalContact::class);

        $query = ExternalContact::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, ExternalContactResource::class));
    }

    /**
     * GET /api/v1/external-contacts/{id}
     */
    public function show(ExternalContact $externalContact): JsonResponse
    {
        $this->authorize('view', $externalContact);

        return response()->json(['data' => new ExternalContactResource($externalContact)]);
    }

    /**
     * POST /api/v1/external-contacts
     */
    public function store(StoreExternalContactRequest $request): JsonResponse
    {
        $this->authorize('create', ExternalContact::class);

        $contact = ExternalContact::create($this->resolvePrimaryContact($request, $request->validated()));

        return response()->json(
            ['data' => new ExternalContactResource($contact->load('organization'))],
            201,
            ['Location' => "/api/v1/external-contacts/{$contact->id}"]
        );
    }

    /**
     * PATCH /api/v1/external-contacts/{id}
     */
    public function update(UpdateExternalContactRequest $request, ExternalContact $externalContact): JsonResponse
    {
        $this->authorize('update', $externalContact);

        $data = $this->resolvePrimaryContact($request, $request->validated(), $externalContact);

        $externalContact->update($data);

        return response()->json(['data' => new ExternalContactResource($externalContact->fresh()->load('organization'))]);
    }

    /**
     * DELETE /api/v1/external-contacts/{id}
     */
    public function destroy(ExternalContact $externalContact): Response
    {
        $this->authorize('delete', $externalContact);

        // Même garde que le contrôleur Blade : un contact lié à des courriers ne
        // se supprime pas — le refus est ici un 409, plus explicite qu'un redirect.
        $mailsCount = $externalContact->sentMails()->count() + $externalContact->receivedMails()->count();

        if ($mailsCount > 0) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Conflit d\'intégrité', 'status' => 409, 'detail' => 'Impossible de supprimer ce contact car il est associé à des courriers.'],
                409
            );
        }

        $externalContact->delete();

        return response()->noContent();
    }

    /**
     * Règle métier : un contact marqué principal retire ce statut aux autres
     * contacts de la même organisation externe.
     *
     * En modification, l'organisation peut ne pas être revalidée dans le payload :
     * elle est alors reprise du contact lui-même (comportement du formulaire Blade,
     * qui renvoyait toujours `external_organization_id`).
     */
    private function resolvePrimaryContact(Request $request, array $data, ?ExternalContact $existing = null): array
    {
        $organizationId = $data['external_organization_id']
            ?? ($existing?->external_organization_id ?? null);

        if (!empty($organizationId) && !empty($data['is_primary_contact'])) {
            ExternalContact::where('external_organization_id', $organizationId)
                ->when($existing, fn ($q) => $q->where('id', '!=', $existing->id))
                ->update(['is_primary_contact' => false]);
        }

        return $data;
    }
}
