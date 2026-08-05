<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\WorkplaceDocument\StoreWorkplaceDocumentRequest;
use App\Http\Resources\Api\V1\WorkplaceDocumentResource;
use App\Models\RecordDigitalDocument;
use App\Models\Workplace;
use App\Models\WorkplaceDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
/**
 * D12 — documents partagés dans un espace de travail (contenu).
 *
 * Relevé contre `WorkplaceContentController` (relu le 2026-08-04). Les partages
 * sont org-scopés via l'autorisation sur le workplace parent (`view` pour la
 * liste, `manageContent` pour les mutations). `shared_by` / `shared_at` posés
 * côté serveur ; l'activité est journalisée comme en Blade.
 */
class WorkplaceDocumentController extends Controller
{
    /**
     * GET /api/v1/workplaces/{workplace}/content/documents
     */
    public function documents(Workplace $workplace): JsonResponse
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        $this->authorize('view', $workplace);

        $documents = $workplace->documents()
            ->with(['document', 'sharedBy'])
            ->latest('shared_at')
            ->get();

        return response()->json(['data' => WorkplaceDocumentResource::collection($documents)]);
    }

    /**
     * POST /api/v1/workplaces/{workplace}/content/documents
     */
    public function shareDocument(StoreWorkplaceDocumentRequest $request, Workplace $workplace): JsonResponse
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        $this->authorize('manageContent', $workplace);

        $data = $request->validated();

        if ($workplace->documents()->where('document_id', $data['document_id'])->exists()) {
            abort(422, 'Ce document est déjà partagé dans ce workspace.');
        }

        $document = $workplace->documents()->create([
            ...$data,
            'shared_by' => Auth::id(),
            'shared_at' => now(),
        ]);

        $workplace->activities()->create([
            'user_id' => Auth::id(),
            'activity_type' => 'shared_document',
            'subject_type' => RecordDigitalDocument::class,
            'subject_id' => $data['document_id'],
            'description' => 'Document partagé',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(
            ['data' => new WorkplaceDocumentResource($document)],
            201,
            ['Location' => "/api/v1/workplaces/{$workplace->id}/content/documents/{$document->id}"]
        );
    }

    /**
     * DELETE /api/v1/workplaces/{workplace}/content/documents/{document}
     */
    public function unshareDocument(Request $request, Workplace $workplace, WorkplaceDocument $document): JsonResponse
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        $this->authorize('manageContent', $workplace);

        $document->delete();

        $workplace->activities()->create([
            'user_id' => Auth::id(),
            'activity_type' => 'deleted_document',
            'description' => 'Partage de document supprimé',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['data' => null]);
    }

    /**
     * POST /api/v1/workplaces/{workplace}/content/documents/{document}/feature
     */
    public function featureDocument(Workplace $workplace, WorkplaceDocument $document): JsonResponse
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        $this->authorize('manageContent', $workplace);

        $document->update(['is_featured' => !$document->is_featured]);

        return response()->json(['data' => new WorkplaceDocumentResource($document->fresh())]);
    }

    private function workplaceInOrganisation(Workplace $workplace): Workplace
    {
        return Workplace::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($workplace->id);
    }
}
