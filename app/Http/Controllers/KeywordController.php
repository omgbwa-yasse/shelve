<?php

namespace App\Http\Controllers;

use App\Models\Keyword;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class KeywordController extends Controller
{
    /**
     * Rechercher des mots-clés pour l'auto-complétion
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $keywords = Keyword::where('name', 'LIKE', "%{$query}%")
                          ->limit(10)
                          ->pluck('name')
                          ->toArray();

        return response()->json($keywords);
    }

    /**
     * Obtenir tous les mots-clés avec comptage d'utilisation
     */
    public function index(): JsonResponse
    {
        $keywords = Keyword::withCount(['records', 'slipRecords'])
                          ->orderBy('name')
                          ->get()
                          ->map(function ($keyword) {
                              return [
                                  'id' => $keyword->id,
                                  'name' => $keyword->name,
                                  'description' => $keyword->description,
                                  'records_count' => $keyword->records_count,
                                  'slip_records_count' => $keyword->slip_records_count,
                                  'total_usage' => $keyword->records_count + $keyword->slip_records_count
                              ];
                          });

        return response()->json($keywords);
    }

    /**
     * Créer un nouveau mot-clé
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:keywords,name',
            'description' => 'nullable|string|max:1000'
        ]);

        $keyword = Keyword::create([
            'name' => trim($request->name),
            'description' => $request->description
        ]);

        return response()->json($keyword, 201);
    }

    /**
     * Mettre à jour un mot-clé
     */
    public function update(Request $request, Keyword $keyword): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:keywords,name,' . $keyword->id,
            'description' => 'nullable|string|max:1000'
        ]);

        $keyword->update([
            'name' => trim($request->name),
            'description' => $request->description
        ]);

        return response()->json($keyword);
    }

    /**
     * Supprimer un mot-clé
     */
    public function destroy(Keyword $keyword): JsonResponse
    {
        // Vérifier s'il est utilisé
        $recordsCount = $keyword->records()->count();
        $slipRecordsCount = $keyword->slipRecords()->count();

        if ($recordsCount > 0 || $slipRecordsCount > 0) {
            return response()->json([
                'error' => 'Ce mot-clé est utilisé par ' . ($recordsCount + $slipRecordsCount) . ' enregistrement(s) et ne peut pas être supprimé.'
            ], 422);
        }

        $keyword->delete();

        return response()->json(['message' => 'Mot-clé supprimé avec succès']);
    }

    /**
     * Traiter une chaîne de mots-clés et retourner les IDs
     */
    public function processKeywords(Request $request): JsonResponse
    {
        $request->validate([
            'keywords' => 'required|string'
        ]);

        $keywords = Keyword::processKeywordsString($request->keywords);

        return response()->json([
            'keywords' => $keywords->map(function ($keyword) {
                return [
                    'id' => $keyword->id,
                    'name' => $keyword->name
                ];
            })
        ]);
    }
}
