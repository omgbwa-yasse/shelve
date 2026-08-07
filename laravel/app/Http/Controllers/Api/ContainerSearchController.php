<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContainerSearchController extends Controller
{
    /**
     * Recherche de containers par code
     * Filtrés par les salles/étagères appartenant à l'organisation courante
     */
    public function search(Request $request)
    {
        $query = $request->input('query', '');

        // Minimum 1 caractère requis
        if (strlen($query) < 1) {
            return response()->json([]);
        }

        $currentOrganisationId = Auth::user()->current_organisation_id;

        // Recherche des containers dont les étagères appartiennent à l'organisation courante
        // La relation Room-Organisation est many-to-many via organisation_room
        $containers = Container::with(['shelf.room'])
            ->whereHas('shelf.room.organisations', function ($q) use ($currentOrganisationId) {
                $q->where('organisations.id', $currentOrganisationId);
            })
            ->where('code', 'LIKE', "%{$query}%")
            ->orderBy('code')
            ->limit(20)
            ->get()
            ->map(function ($container) {
                return [
                    'id' => $container->id,
                    'code' => $container->code,
                    'shelf_code' => $container->shelf->code ?? 'N/A',
                    'room_name' => $container->shelf->room->name ?? 'N/A',
                ];
            });

        return response()->json($containers);
    }
}
