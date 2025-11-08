<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\RecordPhysical;
use App\Models\RecordDigitalFolder;
use App\Models\RecordDigitalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RecordSearchController extends Controller
{
    /**
     * Recherche unifiée dans tous les types de records
     * Cherche dans RecordPhysical, RecordDigitalFolder et RecordDigitalDocument
     * Filtre par les activités de l'organisation courante et leurs descendants
     */
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (!$query || strlen($query) < 3) {
            return response()->json([
                'physical' => [],
                'folders' => [],
                'documents' => [],
                'total' => 0
            ]);
        }

        // Récupérer l'organisation courante de l'utilisateur
        $currentOrgId = Auth::user()->current_organisation_id;

        if (!$currentOrgId) {
            return response()->json([
                'physical' => [],
                'folders' => [],
                'documents' => [],
                'total' => 0
            ]);
        }

        // Récupérer toutes les activités (directes + descendantes)
        $allActivityIds = $this->getAllActivityIds($currentOrgId);

        // Recherche dans les 3 types
        $physicalRecords = $this->searchPhysical($query, $allActivityIds);
        $folders = $this->searchFolders($query, $currentOrgId);
        $documents = $this->searchDocuments($query, $currentOrgId);

        return response()->json([
            'physical' => $physicalRecords,
            'folders' => $folders,
            'documents' => $documents,
            'total' => count($physicalRecords) + count($folders) + count($documents)
        ]);
    }

    /**
     * Recherche dans RecordPhysical
     */
    private function searchPhysical($query, $allActivityIds)
    {
        if (empty($allActivityIds)) {
            return [];
        }

        return RecordPhysical::query()
            ->whereIn('activity_id', $allActivityIds)
            ->where(function($q) use ($query) {
                $q->where('code', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%");
            })
            ->select('id', 'code', 'name', 'content', 'activity_id')
            ->with('activity:id,code,name')
            ->orderBy('code')
            ->limit(50)
            ->get()
            ->map(function($record) {
                return [
                    'id' => $record->id,
                    'code' => $record->code,
                    'name' => $record->name,
                    'content' => $record->content ? Str::limit($record->content, 100) : '',
                    'activity' => $record->activity
                        ? $record->activity->code . ' - ' . $record->activity->name
                        : '',
                    'record_type' => 'physical',
                    'type_label' => 'Dossier Physique'
                ];
            })
            ->toArray();
    }

    /**
     * Recherche dans RecordDigitalFolder
     */
    private function searchFolders($query, $organisationId)
    {
        return RecordDigitalFolder::query()
            ->where('organisation_id', $organisationId)
            ->where(function($q) use ($query) {
                $q->where('code', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->select('id', 'code', 'name', 'description', 'status', 'created_at')
            ->with('creator:id,name')
            ->orderBy('code')
            ->limit(50)
            ->get()
            ->map(function($folder) {
                return [
                    'id' => $folder->id,
                    'code' => $folder->code,
                    'name' => $folder->name,
                    'content' => $folder->description ? Str::limit($folder->description, 100) : '',
                    'status' => $folder->status,
                    'creator' => $folder->creator ? $folder->creator->name : '',
                    'created_at' => $folder->created_at?->format('Y-m-d'),
                    'record_type' => 'folder',
                    'type_label' => 'Dossier Numérique'
                ];
            })
            ->toArray();
    }

    /**
     * Recherche dans RecordDigitalDocument
     */
    private function searchDocuments($query, $organisationId)
    {
        return RecordDigitalDocument::query()
            ->where('organisation_id', $organisationId)
            ->where(function($q) use ($query) {
                $q->where('code', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->select('id', 'code', 'name', 'description', 'version_number', 'status', 'created_at')
            ->with(['creator:id,name', 'folder:id,name'])
            ->orderBy('code')
            ->limit(50)
            ->get()
            ->map(function($document) {
                return [
                    'id' => $document->id,
                    'code' => $document->code,
                    'name' => $document->name,
                    'content' => $document->description ? Str::limit($document->description, 100) : '',
                    'version' => $document->version_number,
                    'status' => $document->status,
                    'folder' => $document->folder ? $document->folder->name : '',
                    'creator' => $document->creator ? $document->creator->name : '',
                    'created_at' => $document->created_at?->format('Y-m-d'),
                    'record_type' => 'document',
                    'type_label' => 'Document Numérique'
                ];
            })
            ->toArray();
    }

    /**
     * Récupère tous les IDs des activités liées à une organisation
     * (activités directes + tous les descendants)
     */
    private function getAllActivityIds($organisationId)
    {
        // Récupérer les activités directement liées à l'organisation
        $directActivityIds = Activity::whereHas('organisations', function($q) use ($organisationId) {
            $q->where('organisations.id', $organisationId);
        })->pluck('id')->toArray();

        // Obtenir tous les descendants
        return $this->getAllDescendantIds($directActivityIds);
    }

    /**
     * Fonction récursive pour obtenir tous les IDs descendants d'activités
     */
    private function getAllDescendantIds(array $activityIds)
    {
        if (empty($activityIds)) {
            return [];
        }

        // Récupérer les enfants directs
        $childIds = Activity::whereIn('parent_id', $activityIds)
            ->pluck('id')
            ->toArray();

        if (empty($childIds)) {
            return $activityIds;
        }

        // Récursion pour obtenir tous les descendants
        $allDescendantIds = $this->getAllDescendantIds($childIds);

        // Fusionner les IDs actuels avec tous les descendants
        return array_unique(array_merge($activityIds, $allDescendantIds));
    }
}
