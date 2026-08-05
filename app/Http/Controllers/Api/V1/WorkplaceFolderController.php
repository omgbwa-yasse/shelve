<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\WorkplaceFolder\StoreWorkplaceFolderRequest;
use App\Http\Resources\Api\V1\WorkplaceFolderResource;
use App\Models\RecordDigitalFolder;
use App\Models\Workplace;
use App\Models\WorkplaceFolder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * D12 — dossiers partagés dans un espace de travail (contenu).
 *
 * Relevé contre `WorkplaceContentController` (relu le 2026-08-04). Les partages
 * sont org-scopés via l'autorisation sur le workplace parent (`view` pour la
 * liste, `manageContent` pour les mutations). `shared_by` / `shared_at` posés
 * côté serveur ; l'activité est journalisée comme en Blade.
 */
class WorkplaceFolderController extends Controller
{
    /**
     * GET /api/v1/workplaces/{workplace}/content/folders
     */
    public function folders(Workplace $workplace): JsonResponse
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        $this->authorize('view', $workplace);

        $folders = $workplace->folders()
            ->with(['folder', 'sharedBy'])
            ->latest('shared_at')
            ->get();

        return response()->json(['data' => WorkplaceFolderResource::collection($folders)]);
    }

    /**
     * POST /api/v1/workplaces/{workplace}/content/folders
     */
    public function shareFolder(StoreWorkplaceFolderRequest $request, Workplace $workplace): JsonResponse
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        $this->authorize('manageContent', $workplace);

        $data = $request->validated();

        if ($workplace->folders()->where('folder_id', $data['folder_id'])->exists()) {
            abort(422, 'Ce dossier est déjà partagé dans ce workspace.');
        }

        $folder = $workplace->folders()->create([
            ...$data,
            'shared_by' => Auth::id(),
            'shared_at' => now(),
        ]);

        $workplace->activities()->create([
            'user_id' => Auth::id(),
            'activity_type' => 'shared_folder',
            'subject_type' => RecordDigitalFolder::class,
            'subject_id' => $data['folder_id'],
            'description' => 'Dossier partagé',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(
            ['data' => new WorkplaceFolderResource($folder)],
            201,
            ['Location' => "/api/v1/workplaces/{$workplace->id}/content/folders/{$folder->id}"]
        );
    }

    /**
     * DELETE /api/v1/workplaces/{workplace}/content/folders/{folder}
     */
    public function unshareFolder(Request $request, Workplace $workplace, WorkplaceFolder $folder): JsonResponse
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        $this->authorize('manageContent', $workplace);

        $folder->delete();

        $workplace->activities()->create([
            'user_id' => Auth::id(),
            'activity_type' => 'deleted_folder',
            'description' => 'Partage de dossier supprimé',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['data' => null]);
    }

    /**
     * POST /api/v1/workplaces/{workplace}/content/folders/{folder}/pin
     */
    public function pinFolder(Workplace $workplace, WorkplaceFolder $folder): JsonResponse
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        $this->authorize('manageContent', $workplace);

        $folder->update(['is_pinned' => !$folder->is_pinned]);

        return response()->json(['data' => new WorkplaceFolderResource($folder->fresh())]);
    }

    private function workplaceInOrganisation(Workplace $workplace): Workplace
    {
        return Workplace::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($workplace->id);
    }
}
