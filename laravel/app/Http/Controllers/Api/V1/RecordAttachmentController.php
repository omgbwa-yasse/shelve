<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RecordAttachment\StoreRecordAttachmentRequest;
use App\Http\Resources\Api\V1\RecordAttachmentResource;
use App\Models\Attachment;
use App\Models\Record;
use App\Models\RecordAttachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * D02 — pièces jointes d'une notice, relu et validé le 2026-08-04 contre
 * `RecordAttachmentController` et le schéma (pivot `record_physical_attachment`,
 * table `attachments` partagée avec D06).
 *
 * Pivot **org-scopée par héritage** de la notice parente (motif D03) : la notice est
 * résolue dans l'organisation courante, puis chaque pièce jointe est bornée à cette
 * notice (404 hors périmètre). Clé composite `(record_id, attachment_id)` sans colonne
 * `id`. L'upload crée l'`Attachment` (fichier + empreintes) puis la pivot — `creator_id`
 * posé depuis l'agent.
 *
 * TODO (non portés en phase 1) :
 *  - `download` / `preview` : serveur de fichiers (Storage + réponses binaires), hors
 *    du contrat de données de la phase 1.
 *  - vignettes : `generateThumbnail` du Blade dépend d'Intervention Image et FFMpeg.
 */
class RecordAttachmentController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['record_id', 'attachment_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['record_id', 'attachment_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['attachment'];

    /**
     * GET /api/v1/records/{record}/attachments
     */
    public function index(Record $record, Request $request): JsonResponse
    {
        $this->authorize('viewAny', RecordAttachment::class);

        $record = Record::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($record->id);

        $query = RecordAttachment::with('attachment')->where('record_id', $record->id);

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE, 'record_id');
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, RecordAttachmentResource::class));
    }

    /**
     * POST /api/v1/records/{record}/attachments/upload
     */
    public function upload(StoreRecordAttachmentRequest $request, Record $record): JsonResponse
    {
        $this->authorize('create', RecordAttachment::class);

        $record = Record::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($record->id);

        $file = $request->file('file');

        $attachment = Attachment::create([
            'path' => $file->store('attachments'),
            'name' => $request->input('name') ?: $file->getClientOriginalName(),
            'crypt' => md5_file($file->getRealPath()),
            'crypt_sha512' => hash_file('sha512', $file->getRealPath()),
            'size' => $file->getSize(),
            'creator_id' => Auth::id(),
            'type' => 'record',
            'mime_type' => $file->getMimeType(),
        ]);

        if ($request->filled('thumbnail')) {
            $thumbnailData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->thumbnail), false);
            $thumbnailPath = 'thumbnails_record/' . $attachment->id . '.jpg';

            if ($thumbnailData !== false && Storage::disk('public')->put($thumbnailPath, $thumbnailData)) {
                $attachment->update(['thumbnail_path' => $thumbnailPath]);
            }
        }

        RecordAttachment::create([
            'record_id' => $record->id,
            'attachment_id' => $attachment->id,
        ]);

        $pivot = RecordAttachment::where('record_id', $record->id)
            ->where('attachment_id', $attachment->id)
            ->with('attachment')
            ->first();

        return response()->json(
            ['data' => new RecordAttachmentResource($pivot)],
            201,
            ['Location' => "/api/v1/records/{$record->id}/attachments/{$attachment->id}"]
        );
    }

    /**
     * DELETE /api/v1/records/{record}/attachments/{attachment}
     */
    public function destroy(Record $record, Attachment $attachment): Response
    {
        $pivot = $this->resolvePivot($record, $attachment);

        $this->authorize('delete', $pivot);

        // Comme en Blade : supprimer la pivot, puis le fichier et l'enregistrement.
        RecordAttachment::where('record_id', $pivot->record_id)
            ->where('attachment_id', $pivot->attachment_id)
            ->delete();
        Storage::delete($attachment->path);
        $attachment->delete();

        return response()->noContent();
    }

    private function resolvePivot(Record $record, Attachment $attachment): RecordAttachment
    {
        $record = Record::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($record->id);

        return RecordAttachment::where('record_id', $record->id)
            ->where('attachment_id', $attachment->id)
            ->firstOrFail();
    }
}
