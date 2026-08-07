<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Attachment;
use App\Models\Record;
use App\Models\RecordAttachment;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\Workplace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * D12 — bibliothèque Documents d'un espace de travail (dossiers + fichiers).
 *
 * Les documents sont des notices (`records`) rattachées à un workplace via
 * `workplace_id` (migration 2026-08-06). `is_workplace_folder` distingue le
 * dossier du fichier ; la hiérarchie réutilise `parent_id`. Les fichiers sont
 * portés par la pivot `record_physical_attachment` (table `attachments`).
 *
 * Isolation : workplace et notices résolus dans l'organisation courante (404
 * hors périmètre). Lecture = `view` du workplace ; écritures = `update`.
 */
class WorkplaceDocumentController extends Controller
{
    /**
     * GET /api/v1/workplaces/{workplace}/documents?parent_id=
     */
    public function index(Request $request, Workplace $workplace): JsonResponse
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        $this->authorize('view', $workplace);

        $query = Record::inOrganisation(Auth::user()->current_organisation_id)
            ->inWorkplace($workplace->id)
            ->withCount('children')
            ->with(['creator', 'attachments']);

        if ($request->filled('parent_id')) {
            $parent = $this->resolveDocument($workplace, (int) $request->input('parent_id'));
            $query->where('parent_id', $parent->id);
        } else {
            $query->whereNull('parent_id');
        }

        $query->orderByDesc('is_workplace_folder')->orderBy('name');

        return response()->json(['data' => $query->get()->map(fn (Record $r) => $this->payload($r))]);
    }

    /**
     * POST /api/v1/workplaces/{workplace}/folders — créer un dossier.
     */
    public function storeFolder(Request $request, Workplace $workplace): JsonResponse
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        $this->authorize('manageContent', $workplace);

        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'parent_id' => 'nullable|integer',
        ]);

        $parent = null;
        if (! empty($validated['parent_id'])) {
            $parent = $this->resolveDocument($workplace, (int) $validated['parent_id']);

            if (! $parent->is_workplace_folder) {
                abort(422, 'Un fichier ne peut pas contenir de dossier.');
            }
        }

        $folder = Record::create([
            'name' => $validated['name'],
            'code' => $this->generateDocumentCode($workplace),
            'parent_id' => $parent?->id,
            'workplace_id' => $workplace->id,
            'is_workplace_folder' => true,
            'organisation_id' => Auth::user()->current_organisation_id,
            'creator_id' => Auth::id(),
            'level_id' => RecordLevel::query()->value('id'),
            'status_id' => RecordStatus::query()->value('id'),
        ]);

        return response()->json(['data' => $this->payload($folder->loadCount('children'))], 201);
    }

    /**
     * POST /api/v1/workplaces/{workplace}/documents/upload — multipart : crée le
     * fichier (notice + pièce jointe) dans le workplace.
     */
    public function upload(Request $request, Workplace $workplace): JsonResponse
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        $this->authorize('manageContent', $workplace);

        $validated = $request->validate([
            'file' => 'required|file|max:102400',
            'name' => 'nullable|string|max:100',
            'parent_id' => 'nullable|integer',
        ]);

        $parent = null;
        if (! empty($validated['parent_id'])) {
            $parent = $this->resolveDocument($workplace, (int) $validated['parent_id']);

            if (! $parent->is_workplace_folder) {
                abort(422, 'Un fichier ne peut pas contenir de document.');
            }
        }

        $file = $request->file('file');

        DB::beginTransaction();
        try {
            $document = Record::create([
                'name' => $request->input('name') ?: $file->getClientOriginalName(),
                'code' => $this->generateDocumentCode($workplace),
                'parent_id' => $parent?->id,
                'workplace_id' => $workplace->id,
                'is_workplace_folder' => false,
                'organisation_id' => Auth::user()->current_organisation_id,
                'creator_id' => Auth::id(),
                'level_id' => RecordLevel::query()->value('id'),
                'status_id' => RecordStatus::query()->value('id'),
            ]);

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

            RecordAttachment::create([
                'record_id' => $document->id,
                'attachment_id' => $attachment->id,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json(
            ['data' => $this->payload($document->fresh(['creator', 'attachments'])->loadCount('children'))],
            201
        );
    }

    /**
     * GET /api/v1/workplaces/{workplace}/documents/{record}/download
     */
    public function download(Workplace $workplace, Record $record)
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        $this->authorize('view', $workplace);

        $document = $this->resolveDocument($workplace, $record);

        $attachment = $document->attachments()->first();

        if (! $attachment) {
            abort(404, 'Ce document n\'a pas de fichier.');
        }

        return Storage::download($attachment->path, $attachment->name);
    }

    /**
     * POST /api/v1/workplaces/{workplace}/documents/{record}/share
     *
     * Rend le document visible du module Records (il reste dans le workplace).
     */
    public function share(Workplace $workplace, Record $record): JsonResponse
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        $this->authorize('manageContent', $workplace);

        $document = $this->resolveDocument($workplace, $record);

        $document->update(['is_workplace_shared' => true]);

        return response()->json(['data' => $this->payload($document->fresh(['creator', 'attachments']))]);
    }

    /**
     * POST /api/v1/workplaces/{workplace}/documents/{record}/unshare
     */
    public function unshare(Workplace $workplace, Record $record): JsonResponse
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        $this->authorize('manageContent', $workplace);

        $document = $this->resolveDocument($workplace, $record);

        $document->update(['is_workplace_shared' => false]);

        return response()->json(['data' => $this->payload($document->fresh(['creator', 'attachments']))]);
    }

    /**
     * POST /api/v1/workplaces/{workplace}/documents/{record}/transfer
     *
     * Transfère le document (et ses descendants) vers le module Records en
     * l'affectant à une classe du plan de classement (`activity_id`). Le document
     * quitte le workplace (`workplace_id = null`) : il n'est plus accessible que
     * via le module Records.
     */
    public function transfer(Request $request, Workplace $workplace, Record $record): JsonResponse
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        $this->authorize('update', $workplace);

        $document = $this->resolveDocument($workplace, $record);

        $validated = $request->validate([
            'activity_id' => 'required|integer|exists:activities,id',
        ]);

        $activity = Activity::findOrFail($validated['activity_id']);

        $ids = $this->collectDescendantIds($document);

        DB::transaction(function () use ($ids, $activity) {
            Record::withTrashed()->whereIn('id', $ids)->update([
                'workplace_id' => null,
                'is_workplace_shared' => false,
                'is_workplace_folder' => false,
                'activity_id' => $activity->id,
            ]);
        });

        return response()->json([
            'data' => [
                'id' => $document->id,
                'transferred' => true,
                'activity' => ['id' => $activity->id, 'name' => $activity->name],
            ],
        ]);
    }

    /**
     * DELETE /api/v1/workplaces/{workplace}/documents/{record}
     *
     * Supprime le dossier/fichier, ses descendants éventuels et les fichiers
     * stockés des pièces jointes.
     */
    public function destroy(Workplace $workplace, Record $record): Response
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        $this->authorize('manageContent', $workplace);

        $document = $this->resolveDocument($workplace, $record);

        $ids = $this->collectDescendantIds($document);

        DB::beginTransaction();
        try {
            $pivots = RecordAttachment::whereIn('record_id', $ids)->get();
            $attachmentIds = $pivots->pluck('attachment_id')->unique();

            foreach (Attachment::whereIn('id', $attachmentIds)->get() as $attachment) {
                Storage::delete($attachment->path);
            }

            Attachment::whereIn('id', $attachmentIds)->delete();
            RecordAttachment::whereIn('record_id', $ids)->delete();
            Record::withTrashed()->whereIn('id', $ids)->forceDelete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->noContent();
    }

    private function generateDocumentCode(Workplace $workplace): string
    {
        do {
            $code = 'WD-' . $workplace->id . '-' . strtoupper(Str::random(6));
        } while (Record::withTrashed()->where('code', $code)->exists());

        return $code;
    }

    private function payload(Record $record): array
    {
        $attachment = $record->relationLoaded('attachments')
            ? $record->attachments->first()
            : null;

        return [
            'id' => $record->id,
            'name' => $record->name,
            'code' => $record->code,
            'workplace_id' => $record->workplace_id,
            'parent_id' => $record->parent_id,
            'is_folder' => (bool) $record->is_workplace_folder,
            'is_shared' => (bool) $record->is_workplace_shared,
            'children_count' => $record->children_count ?? $record->children()->count(),
            'created_at' => $record->created_at?->toIso8601ZuluString(),
            'creator' => $record->creator
                ? ['id' => $record->creator->id, 'name' => $record->creator->name]
                : null,
            'attachment' => $attachment
                ? [
                    'id' => $attachment->id,
                    'name' => $attachment->name,
                    'size' => $attachment->size,
                    'mime_type' => $attachment->mime_type,
                ]
                : null,
        ];
    }

    private function resolveDocument(Workplace $workplace, int|Record $record): Record
    {
        $recordId = $record instanceof Record ? $record->id : (int) $record;

        return Record::inOrganisation(Auth::user()->current_organisation_id)
            ->inWorkplace($workplace->id)
            ->findOrFail($recordId);
    }

    private function collectDescendantIds(Record $record): array
    {
        $ids = [$record->id];

        foreach ($record->children()->pluck('id') as $childId) {
            $child = Record::withTrashed()->find($childId);
            if ($child) {
                $ids = array_merge($ids, $this->collectDescendantIds($child));
            }
        }

        return $ids;
    }

    private function workplaceInOrganisation(Workplace $workplace): Workplace
    {
        return Workplace::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($workplace->id);
    }
}
