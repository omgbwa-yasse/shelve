<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Term;
use App\Models\TermCategory;
use App\Models\TermType;
use Illuminate\Http\Request;

class TermApiController extends Controller
{
    /**
     * Recherche des termes spécifiques (non génériques, non associés) par facettes
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $query = Term::query();

        // Filtrer par mot-clé si fourni
        if ($request->has('keyword') && !empty($request->keyword)) {
            $keyword = $request->keyword;
            $query->where('name', 'like', "%{$keyword}%");
        }

        // Filtrer par catégorie (facette) si fournie
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        // Filtrer par type pour ne montrer que les termes spécifiques (non génériques, non associés)
        // Ceci suppose que les termes spécifiques ont un type_id spécifique (à ajuster selon votre modèle)
        $specificTypeIds = TermType::where('name', 'like', '%specific%')
                           ->orWhere('is_specific', true)
                           ->pluck('id')
                           ->toArray();

        if (!empty($specificTypeIds)) {
            $query->whereIn('type_id', $specificTypeIds);
        } else {
            // Si aucun type spécifique n'est trouvé, filtrer pour exclure les termes génériques ou associés
            // Ceci suppose que vous avez un champ parent_id pour les termes associés/génériques
            $query->whereNull('parent_id');
        }        // Limiter le nombre de résultats pour des raisons de performance
        $terms = $query->with('category:id,name')->limit(50)->get(['id', 'name', 'category_id']);

        // Formater les résultats avec le nom de la catégorie
        $formattedTerms = $terms->map(function ($term) {
            $categoryName = $term->category ? ucfirst($term->category->name) : '';

            return [
                'id' => $term->id,
                'name' => $term->name,
                'category_id' => $term->category_id,
                'category_name' => $categoryName,
                'formatted_name' => $term->name . ($categoryName ? '(' . $categoryName . ')' : '')
            ];
        });

        return response()->json($formattedTerms);
    }

    /**
     * Retourne les catégories de termes disponibles (facettes)
     *
     * @return \Illuminate\Http\Response
     */
    public function categories()
    {
        $categories = TermCategory::all(['id', 'name']);
        return response()->json($categories);
    }
}
