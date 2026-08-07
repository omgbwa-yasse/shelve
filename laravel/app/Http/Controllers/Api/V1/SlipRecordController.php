<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SlipRecord\StoreSlipRecordRequest;
use App\Http\Requests\Api\V1\SlipRecord\UpdateSlipRecordRequest;
use App\Http\Resources\Api\V1\SlipRecordResource;
use App\Models\Keyword;
use App\Models\Slip;
use App\Models\SlipRecord;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D04 — relu et validé le 2026-08-04 contre `SlipRecordController` et le schéma.
 *
 * Les documents de bordereau sont **org-scopés** par leur bordereau parent (motif D03) :
 * le `Slip` parent est résolu dans l'organisation courante, puis chaque document est
 * borné au `slip_id` de ce bordereau (une ressource hors périmètre répond 404).
 * `date_format` est dérivé serveur de `date_start`/`date_end` (comme en Blade),
 * `creator_id` est posé depuis l'agent authentifié.
 *
 * TODO : `getDateFormat` du Blade échoue si `date_start`/`date_end` sont vides (DateTime
 * sur null) ; ici le calcul est protégé et le format retombe sur 'D'.
 */
class SlipRecordController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'slip_id', 'code', 'date_format', 'date_start', 'date_end', 'date_exact', 'level_id', 'width', 'support_id', 'activity_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'slip_id', 'code', 'date_format', 'date_start', 'date_end', 'date_exact', 'level_id', 'width', 'support_id', 'activity_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['slip', 'level', 'support', 'activity', 'creator', 'containers', 'keywords', 'attachments'];

    /**
     * GET /api/v1/slips/{slip}/records
     */
    public function index(Slip $slip, Request $request): JsonResponse
    {
        $this->authorize('viewAny', SlipRecord::class);

        $slip = Slip::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($slip->id);

        $query = $slip->records()->getQuery();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, SlipRecordResource::class));
    }

    /**
     * GET /api/v1/slips/{slip}/records/{id}
     */
    public function show(Slip $slip, SlipRecord $slipRecord): JsonResponse
    {
        $this->authorize('view', $slipRecord);

        $slip = Slip::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($slip->id);
        $slipRecord = $slip->records()->whereKey($slipRecord->id)->firstOrFail();

        return response()->json(['data' => new SlipRecordResource($slipRecord)]);
    }

    /**
     * POST /api/v1/slips/{slip}/records
     */
    public function store(StoreSlipRecordRequest $request, Slip $slip): JsonResponse
    {
        $this->authorize('create', SlipRecord::class);

        $slip = Slip::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($slip->id);

        $dateFormat = $this->computeDateFormat($request->input('date_start'), $request->input('date_end'));

        if (strlen($dateFormat) > 1) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Validation', 'status' => 422, 'detail' => 'Le format de date doit tenir sur un seul caractère.', 'errors' => ['date_format' => ['Le format de date doit tenir sur un seul caractère.']]],
                422
            );
        }

        $slipRecord = SlipRecord::create($request->validated() + [
            'slip_id' => $slip->id,
            'date_format' => $dateFormat,
            'creator_id' => Auth::id(),
        ]);

        // Attachement du contenant via la pivot si fourni (comme en Blade).
        if ($request->filled('container_id')) {
            $slipRecord->containers()->attach($request->input('container_id'), [
                'creator_id' => Auth::id(),
                'description' => $request->input('name'),
            ]);
        }

        // Traitement des mots-clés (comme en Blade).
        if ($request->filled('keywords')) {
            $keywords = Keyword::processKeywordsString($request->keywords);
            $slipRecord->keywords()->attach($keywords->pluck('id'));
        }

        return response()->json(
            ['data' => new SlipRecordResource($slipRecord->fresh())],
            201,
            ['Location' => "/api/v1/slips/{$slip->id}/records/{$slipRecord->id}"]
        );
    }

    /**
     * PATCH /api/v1/slips/{slip}/records/{id}
     */
    public function update(UpdateSlipRecordRequest $request, Slip $slip, SlipRecord $slipRecord): JsonResponse
    {
        $this->authorize('update', $slipRecord);

        $slip = Slip::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($slip->id);
        $slipRecord = $slip->records()->whereKey($slipRecord->id)->firstOrFail();

        $dateFormat = $this->computeDateFormat($request->input('date_start'), $request->input('date_end'));

        if (strlen($dateFormat) > 1) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Validation', 'status' => 422, 'detail' => 'Le format de date doit tenir sur un seul caractère.', 'errors' => ['date_format' => ['Le format de date doit tenir sur un seul caractère.']]],
                422
            );
        }

        $slipRecord->update($request->validated() + ['date_format' => $dateFormat]);

        // Gestion des contenants via la pivot (comme en Blade).
        if ($request->has('container_ids')) {
            if (!empty($request->input('container_ids'))) {
                $containersData = [];
                foreach ($request->input('container_ids') as $containerId) {
                    $containersData[$containerId] = [
                        'creator_id' => Auth::id(),
                        'description' => 'Association via édition SlipRecord - ' . now()->format('Y-m-d H:i'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                $slipRecord->containers()->sync($containersData);
            } else {
                $slipRecord->containers()->detach();
            }
        }

        // Traitement des mots-clés (comme en Blade).
        if ($request->filled('keywords')) {
            $keywords = Keyword::processKeywordsString($request->keywords);
            $slipRecord->keywords()->sync($keywords->pluck('id'));
        } elseif ($request->has('keywords')) {
            $slipRecord->keywords()->detach();
        }

        return response()->json(['data' => new SlipRecordResource($slipRecord->fresh())]);
    }

    /**
     * DELETE /api/v1/slips/{slip}/records/{id}
     */
    public function destroy(Slip $slip, SlipRecord $slipRecord): Response
    {
        $this->authorize('delete', $slipRecord);

        $slip = Slip::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($slip->id);
        $slipRecord = $slip->records()->whereKey($slipRecord->id)->firstOrFail();

        // Comme en Blade : détacher d'abord les pièces jointes, puis supprimer.
        $slipRecord->attachments()->detach();
        $slipRecord->delete();

        return response()->noContent();
    }

    private function computeDateFormat(?string $dateStart, ?string $dateEnd): string
    {
        if (!$dateStart || !$dateEnd) {
            return 'D';
        }

        $start = new DateTime($dateStart);
        $end = new DateTime($dateEnd);

        if ($start->format('Y') !== $end->format('Y')) {
            return 'Y';
        } elseif ($start->format('m') !== $end->format('m')) {
            return 'M';
        }

        return 'D';
    }
}
