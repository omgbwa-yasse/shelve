<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SlipRecordAttachment\StoreSlipRecordAttachmentRequest;
use App\Http\Resources\Api\V1\SlipRecordAttachmentResource;
use App\Models\Attachment;
use App\Models\Slip;
use App\Models\SlipRecord;
use App\Models\SlipRecordAttachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * D04 — relu et validé le 2026-08-04 contre `slipRecordAttachmentController` et le schéma.
 *
 * Pièces jointes d'un document de bordereau, **org-scopées** par héritage (motif D03) :
 * le `Slip`/`SlipRecord` parents sont résolus dans l'organisation courante. La pivot
 * `slip_record_attachments` n'a pas de colonne `id` : chaque ressource est résolue sur
 * `(slip_record_id, attachment_id)`. L'upload crée l'`Attachment` (fichier + empreintes)
 * puis la pivot — `creator_id` posé depuis l'agent.
 *
 * TODO (non portés en phase 1) :
 *  - `show` / `download` / `preview` : serveur de fichiers (Storage + réponses binaires),
 *    hors du contrat de données de la phase 1.
 */
class SlipRecordAttachmentController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['slip_record_id', 'attachment_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['slip_record_id', 'attachment_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['slipRecord', 'attachment'];

    /**
     * GET /api/v1/slips/{slip}/records/{record}/attachments
     */
    public function index(Slip $slip, SlipRecord $record, Request $request): JsonResponse
    {
        $this->authorize('viewAny', SlipRecordAttachment::class);

        $slip = Slip::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($slip->id);
        $record = $slip->records()->whereKey($record->id)->firstOrFail();

        $query = SlipRecordAttachment::with('attachment')->where('slip_record_id', $record->id);

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, SlipRecordAttachmentResource::class));
    }

    /**
     * POST /api/v1/slips/{slip}/records/{record}/attachments/upload
     */
    public function upload(StoreSlipRecordAttachmentRequest $request, Slip $slip, SlipRecord $record): JsonResponse
    {
        $this->authorize('create', SlipRecordAttachment::class);

        $slip = Slip::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($slip->id);
        $record = $slip->records()->whereKey($record->id)->firstOrFail();

        $file = $request->file('file');

        $attachment = Attachment::create([
            'path' => $file->store('attachments'),
            'name' => $file->getClientOriginalName(),
            'crypt' => md5_file($file->getRealPath()),
            'crypt_sha512' => hash_file('sha512', $file->getRealPath()),
            'size' => $file->getSize(),
            'creator_id' => Auth::id(),
            'type' => 'transferring',
        ]);

        if ($request->filled('thumbnail')) {
            $thumbnailData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->thumbnail), false);
            $thumbnailPath = 'thumbnails/' . $attachment->id . '.jpg';

            if ($thumbnailData !== false && Storage::disk('public')->put($thumbnailPath, $thumbnailData)) {
                $attachment->update(['thumbnail_path' => $thumbnailPath]);
            }
        }

        SlipRecordAttachment::create([
            'slip_record_id' => $record->id,
            'attachment_id' => $attachment->id,
        ]);

        $pivot = SlipRecordAttachment::where('slip_record_id', $record->id)
            ->where('attachment_id', $attachment->id)
            ->with('attachment')
            ->first();

        return response()->json(['data' => new SlipRecordAttachmentResource($pivot)], 201);
    }

    /**
     * DELETE /api/v1/slips/{slip}/records/{record}/attachments/{attachment}
     */
    public function destroy(Slip $slip, SlipRecord $record, Attachment $attachment): Response
    {
        $pivot = $this->resolvePivot($slip, $record, $attachment);

        $this->authorize('delete', $pivot);

        // Comme en Blade : supprimer la pivot, puis le fichier et l'enregistrement.
        // Clé composite sans colonne `id` : suppression via query builder.
        SlipRecordAttachment::where('slip_record_id', $pivot->slip_record_id)
            ->where('attachment_id', $pivot->attachment_id)
            ->delete();
        Storage::delete($attachment->path);
        $attachment->delete();

        return response()->noContent();
    }

    private function resolvePivot(Slip $slip, SlipRecord $record, Attachment $attachment): SlipRecordAttachment
    {
        $slip = Slip::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($slip->id);
        $record = $slip->records()->whereKey($record->id)->firstOrFail();

        return SlipRecordAttachment::where('slip_record_id', $record->id)
            ->where('attachment_id', $attachment->id)
            ->firstOrFail();
    }
}
