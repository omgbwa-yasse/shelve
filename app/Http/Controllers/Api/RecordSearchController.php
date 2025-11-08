<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\RecordPhysical;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RecordSearchController extends Controller
{
    /**
     * Recherche des documents par code, titre ou résumé
     * Filtre par les activités de l'organisation courante et leurs descendants
     */
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (!$query || strlen($query) < 3) {
            return response()->json([]);
        }

        // Récupérer l'organisation courante de l'utilisateur
        $currentOrgId = Auth::user()->current_organisation_id;

        if (!$currentOrgId) {
            return response()->json([]);
        }

        // Récupérer toutes les activités (directes + descendantes)
        $allActivityIds = $this->getAllActivityIds($currentOrgId);

        if (empty($allActivityIds)) {
            return response()->json([]);
        }

        // Recherche des records
        $records = RecordPhysical::query()
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
                ];
            });

        return response()->json($records);
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
