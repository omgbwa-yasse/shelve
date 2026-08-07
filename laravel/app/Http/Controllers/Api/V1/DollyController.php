<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Dolly\StoreDollyRequest;
use App\Http\Requests\Api\V1\Dolly\UpdateDollyRequest;
use App\Http\Resources\Api\V1\DollyResource;
use App\Models\Communication;
use App\Models\Container;
use App\Models\Dolly;
use App\Models\Mail;
use App\Models\Record;
use App\Models\Room;
use App\Models\Shelf;
use App\Models\Slip;
use App\Models\SlipRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D11 — chariots (dollies), relu le 2026-08-04 contre `DollyController` (Blade) et
 * le schéma.
 *
 * Les chariots sont **org-scopés** par `owner_organisation_id` (motif D03 appliqué
 * via `Dolly::inOrganisation()`, voir la portée ajoutée au modèle) : index borné à
 * l'organisation courante, ressource d'une autre organisation en 404.
 * `created_by`, `owner_organisation_id` et `is_public` (forcé à false) sont posés
 * depuis l'agent, jamais acceptés du client.
 *
 * Les actions d'ajout/retrait (records, mails, communications, salles, boîtes,
 * étagères, descriptions de versement, dossiers/documents numériques) sont portées
 * en POST/DELETE, rejointes le 2026-08-05 par `addSlip`/`removeSlip` (slips), `clear`
 * (détachement de tous les éléments) et `rename` (renommage). `createWithCommunications`
 * et `createWithRecords` n'existent pas dans le contrôleur Blade : TODO documenté
 * (voir l'en-tête).
 */
class DollyController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'name', 'category', 'is_public', 'owner_organisation_id', 'created_by', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'name', 'category', 'is_public', 'owner_organisation_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['creator', 'ownerOrganisation'];

    /**
     * GET /api/v1/dollies
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Dolly::class);

        $query = Dolly::inOrganisation(Auth::user()->current_organisation_id);

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, DollyResource::class));
    }

    /**
     * GET /api/v1/dollies/{id}
     */
    public function show(Dolly $dolly): JsonResponse
    {
        $this->authorize('view', $dolly);

        $dolly = Dolly::inOrganisation(Auth::user()->current_organisation_id)
            ->with(['creator', 'ownerOrganisation'])
            ->findOrFail($dolly->id);

        return response()->json(['data' => new DollyResource($dolly)]);
    }

    /**
     * POST /api/v1/dollies
     */
    public function store(StoreDollyRequest $request): JsonResponse
    {
        $this->authorize('create', Dolly::class);

        $dolly = Dolly::create($request->validated() + [
            'is_public' => false,
            'created_by' => Auth::id(),
            'owner_organisation_id' => Auth::user()->current_organisation_id,
        ]);

        return response()->json(
            ['data' => new DollyResource($dolly->load('creator', 'ownerOrganisation'))],
            201,
            ['Location' => "/api/v1/dollies/{$dolly->id}"]
        );
    }

    /**
     * PATCH /api/v1/dollies/{id}
     */
    public function update(UpdateDollyRequest $request, Dolly $dolly): JsonResponse
    {
        $this->authorize('update', $dolly);

        $dolly = Dolly::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($dolly->id);

        $dolly->update($request->validated() + ['is_public' => false]);

        return response()->json(['data' => new DollyResource($dolly->fresh())]);
    }

    /**
     * DELETE /api/v1/dollies/{id}
     */
    public function destroy(Dolly $dolly): JsonResponse|Response
    {
        $this->authorize('delete', $dolly);

        $dolly = Dolly::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($dolly->id);

        if ($dolly->mails()->exists()
            || $dolly->records()->exists()
            || $dolly->communications()->exists()
            || $dolly->slips()->exists()
            || $dolly->slipRecords()->exists()
            || $dolly->buildings()->exists()
            || $dolly->rooms()->exists()
            || $dolly->shelve()->exists()
        ) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Validation', 'status' => 422, 'detail' => 'Impossible de supprimer un chariot qui contient encore des éléments.'],
                422
            );
        }

        $dolly->delete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/dollies/list
     *
     * Chariots « mail » de l'organisation courante (action `apiList` du Blade),
     * avec recherche `q` sur le nom.
     */
    public function apiList(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Dolly::class);

        $query = Dolly::where('category', 'mail')
            ->inOrganisation(Auth::user()->current_organisation_id);

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->input('q') . '%');
        }

        return response()->json(['data' => DollyResource::collection($query->get())]);
    }

    /**
     * POST /api/v1/dollies/store
     *
     * Création rapide d'un chariot « mail » (action `apiCreate` du Blade).
     */
    public function apiCreate(Request $request): JsonResponse
    {
        $this->authorize('create', Dolly::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $dolly = Dolly::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'category' => 'mail',
            'is_public' => false,
            'created_by' => Auth::id(),
            'owner_organisation_id' => Auth::user()->current_organisation_id,
        ]);

        return response()->json(['data' => new DollyResource($dolly)], 201);
    }

    // ==================== AJOUT ====================

    public function addRecord(Request $request, Dolly $dolly): JsonResponse
    {
        $this->authorize('update', $dolly);
        $dolly = $this->inOrg($dolly);

        $request->validate(['record_id' => 'required|exists:records,id']);
        $dolly->records()->syncWithoutDetaching($request->input('record_id'));

        return $this->updatedDolly($dolly);
    }

    public function addMail(Request $request, Dolly $dolly): JsonResponse
    {
        $this->authorize('update', $dolly);
        $dolly = $this->inOrg($dolly);

        $request->validate(['mail_id' => 'required|exists:mails,id']);
        $dolly->mails()->syncWithoutDetaching($request->input('mail_id'));

        return $this->updatedDolly($dolly);
    }

    public function addCommunication(Request $request, Dolly $dolly): JsonResponse
    {
        $this->authorize('update', $dolly);
        $dolly = $this->inOrg($dolly);

        $request->validate(['communication_id' => 'required|exists:communications,id']);
        $dolly->communications()->syncWithoutDetaching($request->input('communication_id'));

        return $this->updatedDolly($dolly);
    }

    public function addRoom(Request $request, Dolly $dolly): JsonResponse
    {
        $this->authorize('update', $dolly);
        $dolly = $this->inOrg($dolly);

        $request->validate(['room_id' => 'required|exists:rooms,id']);
        $dolly->rooms()->syncWithoutDetaching($request->input('room_id'));

        return $this->updatedDolly($dolly);
    }

    public function addContainer(Request $request, Dolly $dolly): JsonResponse
    {
        $this->authorize('update', $dolly);
        $dolly = $this->inOrg($dolly);

        $request->validate(['container_id' => 'required|exists:containers,id']);
        $dolly->containers()->syncWithoutDetaching($request->input('container_id'));

        return $this->updatedDolly($dolly);
    }

    public function addShelve(Request $request, Dolly $dolly): JsonResponse
    {
        $this->authorize('update', $dolly);
        $dolly = $this->inOrg($dolly);

        $request->validate(['shelve_id' => 'required|exists:shelves,id']);
        $dolly->shelve()->syncWithoutDetaching($request->input('shelve_id'));

        return $this->updatedDolly($dolly);
    }

    public function addSlipRecord(Request $request, Dolly $dolly): JsonResponse
    {
        $this->authorize('update', $dolly);
        $dolly = $this->inOrg($dolly);

        $request->validate(['slip_record_id' => 'required|exists:slip_records,id']);
        $dolly->slipRecords()->syncWithoutDetaching($request->input('slip_record_id'));

        return $this->updatedDolly($dolly);
    }

    public function addSlip(Request $request, Dolly $dolly): JsonResponse
    {
        $this->authorize('update', $dolly);
        $dolly = $this->inOrg($dolly);

        $request->validate(['slip_id' => 'required|exists:slips,id']);
        $dolly->slips()->syncWithoutDetaching($request->input('slip_id'));

        return $this->updatedDolly($dolly);
    }

    /**
     * Renomme le chariot (action `new_name` de `DollyActionController`, forme simple).
     */
    public function rename(Request $request, Dolly $dolly): JsonResponse
    {
        $this->authorize('update', $dolly);
        $dolly = $this->inOrg($dolly);

        $request->validate(['name' => 'required|string|max:255']);
        $dolly->update(['name' => $request->input('name')]);

        return $this->updatedDolly($dolly);
    }

    /**
     * Vide le chariot : détache tous ses éléments (actions `clean` du Blade
     * `DollyActionController`, qui ne détachent que les pivots, sans supprimer
     * les entités sous-jacentes).
     */
    public function clear(Dolly $dolly): JsonResponse
    {
        $this->authorize('update', $dolly);
        $dolly = $this->inOrg($dolly);

        $dolly->mails()->detach();
        $dolly->records()->detach();
        $dolly->communications()->detach();
        $dolly->slips()->detach();
        $dolly->slipRecords()->detach();
        $dolly->buildings()->detach();
        $dolly->rooms()->detach();
        $dolly->shelve()->detach();
        $dolly->containers()->detach();

        return $this->updatedDolly($dolly);
    }

    // ==================== RETRAIT ====================

    public function removeRecord(Dolly $dolly, Record $record): Response
    {
        $this->authorize('update', $dolly);
        $dolly = $this->inOrg($dolly);

        $dolly->records()->detach($record->id);

        return response()->noContent();
    }

    public function removeMail(Dolly $dolly, Mail $mail): Response
    {
        $this->authorize('update', $dolly);
        $dolly = $this->inOrg($dolly);

        $dolly->mails()->detach($mail->id);

        return response()->noContent();
    }

    public function removeCommunication(Dolly $dolly, Communication $communication): Response
    {
        $this->authorize('update', $dolly);
        $dolly = $this->inOrg($dolly);

        $dolly->communications()->detach($communication->id);

        return response()->noContent();
    }

    public function removeRoom(Dolly $dolly, Room $room): Response
    {
        $this->authorize('update', $dolly);
        $dolly = $this->inOrg($dolly);

        $dolly->rooms()->detach($room->id);

        return response()->noContent();
    }

    public function removeContainer(Dolly $dolly, Container $container): Response
    {
        $this->authorize('update', $dolly);
        $dolly = $this->inOrg($dolly);

        $dolly->containers()->detach($container->id);

        return response()->noContent();
    }

    public function removeShelve(Dolly $dolly, Shelf $shelve): Response
    {
        $this->authorize('update', $dolly);
        $dolly = $this->inOrg($dolly);

        $dolly->shelve()->detach($shelve->id);

        return response()->noContent();
    }

    public function removeSlipRecord(Dolly $dolly, SlipRecord $slipRecord): Response
    {
        $this->authorize('update', $dolly);
        $dolly = $this->inOrg($dolly);

        $dolly->slipRecords()->detach($slipRecord->id);

        return response()->noContent();
    }

    public function removeSlip(Dolly $dolly, Slip $slip): Response
    {
        $this->authorize('update', $dolly);
        $dolly = $this->inOrg($dolly);

        $dolly->slips()->detach($slip->id);

        return response()->noContent();
    }

    // ==================== OUTILS ====================

    /**
     * Ré-affecte le chariot dans l'organisation courante (motif D03) : 404 sinon.
     */
    private function inOrg(Dolly $dolly): Dolly
    {
        return Dolly::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($dolly->id);
    }

    private function updatedDolly(Dolly $dolly): JsonResponse
    {
        return response()->json(['data' => new DollyResource($dolly->fresh())]);
    }
}
