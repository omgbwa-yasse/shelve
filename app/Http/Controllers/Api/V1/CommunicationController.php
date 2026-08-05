<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Communication\StoreCommunicationRequest;
use App\Http\Requests\Api\V1\Communication\UpdateCommunicationRequest;
use App\Http\Resources\Api\V1\CommunicationResource;
use App\Models\communicationRecord;
use App\Models\Communication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D05 — relu et validé le 2026-08-04 contre `CommunicationController` et le schéma.
 *
 * Les communications sont **org-scopées** (double organisation opérateur/bénéficiaire,
 * `HasDualOrganisation`) : l'index n'expose que les communications impliquant
 * l'organisation courante, et une ressource hors périmètre répond 404 (motif D03, R03).
 * `code` est généré serveur, `operator_id` / `operator_organisation_id` posés depuis
 * l'agent authentifié. Les transitions de statut passent par les routes d'action
 * (`validate`, `reject`, `transmit`, `return-effective`, `return-cancel`).
 *
 * TODO (non portés en phase 1) :
 *  - `addToCart` : panier côté session/front, hors contrat de données.
 *  - `export` / `print` : exports Excel et PDF via services dédiés.
 *  - `transmission` du Blade : `changeStatus('transmit')` déclenché sans garde d'état —
 *    porté ici avec la même règle (uniquement si approuvé et non retourné, via le modèle).
 */
class CommunicationController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'code', 'name', 'operator_organisation_id', 'user_id', 'user_organisation_id', 'return_date', 'return_effective', 'status', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'code', 'name', 'operator_organisation_id', 'user_id', 'user_organisation_id', 'return_date', 'return_effective', 'status', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['operator', 'operatorOrganisation', 'user', 'userOrganisation', 'records'];

    /**
     * GET /api/v1/communications
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Communication::class);

        $query = Communication::forOrganisation(Auth::user()->current_organisation_id);

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, CommunicationResource::class));
    }

    /**
     * GET /api/v1/communications/{id}
     */
    public function show(Communication $communication): JsonResponse
    {
        $this->authorize('view', $communication);

        $communication = Communication::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($communication->id);

        return response()->json(['data' => new CommunicationResource($communication)]);
    }

    /**
     * POST /api/v1/communications
     */
    public function store(StoreCommunicationRequest $request): JsonResponse
    {
        $this->authorize('create', Communication::class);

        // Comme en Blade : le code est généré serveur.
        $communication = Communication::create($request->validated() + [
            'code' => (new \App\Services\CodeGeneratorService())->generateCommunicationCode(),
            'operator_id' => Auth::id(),
            'operator_organisation_id' => Auth::user()->current_organisation_id,
        ]);

        return response()->json(
            ['data' => new CommunicationResource($communication)],
            201,
            ['Location' => "/api/v1/communications/{$communication->id}"]
        );
    }

    /**
     * PATCH /api/v1/communications/{id}
     */
    public function update(UpdateCommunicationRequest $request, Communication $communication): JsonResponse
    {
        $this->authorize('update', $communication);

        $communication = Communication::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($communication->id);

        // Comme en Blade : une communication retournée n'est plus modifiable.
        if ($communication->isReturned()) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Conflit', 'status' => 409, 'detail' => 'Cette communication ne peut plus être modifiée car elle a été retournée.'],
                409
            );
        }

        $communication->update($request->validated());

        return response()->json(['data' => new CommunicationResource($communication->fresh())]);
    }

    /**
     * DELETE /api/v1/communications/{id}
     */
    public function destroy(Communication $communication): Response
    {
        $this->authorize('delete', $communication);

        $communication = Communication::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($communication->id);

        // Comme en Blade : refus si retournée ou si elle contient des documents.
        if ($communication->isReturned()) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Conflit', 'status' => 409, 'detail' => 'Cette communication ne peut pas être supprimée car elle a été retournée.'],
                409
            );
        }

        if ($communication->records()->exists()) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Conflit', 'status' => 409, 'detail' => 'Vous ne pouvez pas supprimer cette communication car elle contient des documents.'],
                409
            );
        }

        $communication->delete();

        return response()->noContent();
    }

    /**
     * POST /api/v1/communications/{communication}/validate — validation d'une demande.
     */
    public function validateCommunication(Communication $communication): JsonResponse
    {
        $this->authorize('update', $communication);

        $communication = Communication::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($communication->id);

        if ($communication->isPending() && !$communication->isReturned()) {
            $communication->changeStatus('validate');

            return response()->json(['data' => new CommunicationResource($communication->fresh())]);
        }

        return $this->transitionRefused();
    }

    /**
     * POST /api/v1/communications/{communication}/reject — rejet d'une demande.
     */
    public function reject(Request $request, Communication $communication): JsonResponse
    {
        $this->authorize('update', $communication);

        $communication = Communication::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($communication->id);

        if (($communication->isPending() || $communication->isApproved()) && !$communication->isReturned()) {
            $communication->changeStatus('reject');

            if ($request->filled('reason')) {
                $communication->update(['content' => $communication->content . "\n\nRaison du rejet: " . $request->input('reason')]);
            }

            return response()->json(['data' => new CommunicationResource($communication->fresh())]);
        }

        return $this->transitionRefused();
    }

    /**
     * POST /api/v1/communications/{communication}/transmit — mise en consultation.
     */
    public function transmission(Communication $communication): JsonResponse
    {
        $this->authorize('update', $communication);

        $communication = Communication::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($communication->id);

        if ($communication->return_effective === null && !$communication->isReturned()) {
            $communication->changeStatus('transmit');

            return response()->json(['data' => new CommunicationResource($communication->fresh())]);
        }

        return $this->transitionRefused();
    }

    /**
     * POST /api/v1/communications/{communication}/return-effective — retour effectif.
     */
    public function returnEffective(Communication $communication): JsonResponse
    {
        $this->authorize('update', $communication);

        $communication = Communication::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($communication->id);

        if ($communication->return_effective === null) {
            $communication->changeStatus('return');
            $communication->update(['return_effective' => now()]);

            // Le Blade visait la pivot `communication_record` : on met à jour les
            // enregistrements liés non encore retournés (intention d'origine).
            communicationRecord::where('communication_id', $communication->id)
                ->whereNull('return_effective')
                ->update(['return_effective' => now()]);

            return response()->json(['data' => new CommunicationResource($communication->fresh())]);
        }

        return $this->transitionRefused();
    }

    /**
     * POST /api/v1/communications/{communication}/return-cancel — annulation du retour.
     */
    public function returnCancel(Communication $communication): JsonResponse
    {
        $this->authorize('update', $communication);

        $communication = Communication::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($communication->id);

        if ($communication->return_effective !== null) {
            $communication->changeStatus('cancel_return');
            $communication->update(['return_effective' => null]);

            communicationRecord::where('communication_id', $communication->id)
                ->whereNotNull('return_effective')
                ->update(['return_effective' => null]);

            return response()->json(['data' => new CommunicationResource($communication->fresh())]);
        }

        return $this->transitionRefused();
    }

    private function transitionRefused(): JsonResponse
    {
        return response()->json(
            ['type' => 'about:blank', 'title' => 'Conflit', 'status' => 409, 'detail' => 'Cette communication ne peut pas changer de statut dans son état actuel.'],
            409
        );
    }
}
