<?php

namespace App\Http\Controllers;

use App\Models\RecordPhysical;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PublicAutocompleteController extends Controller
{
    /**
     * Autocomplétion publique pour les records
     */
    public function records(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:3|max:255',
            'limit' => 'nullable|integer|min:1|max:5',
        ]);

        $query = $request->get('q');
        $limit = $request->get('limit', 5);

        // Recherche dans les records par nom et code
        $records = RecordPhysical::where(function($q) use ($query) {
            $q->where('name', 'LIKE', '%' . $query . '%')
              ->orWhere('code', 'LIKE', '%' . $query . '%');
        })
        ->select('id', 'name', 'code')
        ->limit($limit)
        ->get();

        $suggestions = $records->map(function ($record) {
            return [
                'id' => $record->id,
                'label' => $record->name . ' (' . $record->code . ')',
                'name' => $record->name,
                'code' => $record->code
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $suggestions
        ]);
    }
}
