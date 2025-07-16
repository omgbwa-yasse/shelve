<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\Models\ThesaurusConcept;
use App\Models\ThesaurusScheme;
use App\Models\ThesaurusLabel;
use App\Models\ThesaurusConceptNote;
use App\Models\ThesaurusConceptRelation;
use App\Models\ThesaurusOrganization;
use App\Models\ThesaurusNamespace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ThesaurusController extends Controller
{
    /**
     * Afficher la page d'accueil du module thésaurus
     */
    public function index()
    {
        $schemes = ThesaurusScheme::with('concepts')->get();
        $stats = [
            'total_schemes' => ThesaurusScheme::count(),
            'total_concepts' => ThesaurusConcept::count(),
            'total_labels' => ThesaurusLabel::count(),
            'total_relations' => ThesaurusConceptRelation::count(),
            'records_with_concepts' => Record::has('thesaurusConcepts')->count(),
        ];

        return view('thesaurus.tool.index', compact('schemes', 'stats'));
    }

    /**
     * Afficher la liste des concepts du thésaurus
     */
    public function create()
    {
        return view('thesaurus.create');
    }

    /**
     * Stocker un nouveau concept
     */
    public function store(Request $request)
    {
        // Logic pour créer un nouveau concept
    }

    /**
     * Afficher un concept spécifique
     */
    public function show(ThesaurusConcept $thesaurus)
    {
        return view('thesaurus.show', compact('thesaurus'));
    }

    /**
     * Afficher le formulaire d'édition d'un concept
     */
    public function edit(ThesaurusConcept $thesaurus)
    {
        return view('thesaurus.edit', compact('thesaurus'));
    }

    /**
     * Mettre à jour un concept
     */
    public function update(Request $request, ThesaurusConcept $thesaurus)
    {
        // Logic pour mettre à jour le concept
    }

    /**
     * Supprimer un concept
     */
    public function destroy(ThesaurusConcept $thesaurus)
    {
        // Logic pour supprimer le concept
    }

    /**
     * Gestion des imports et exports
     */
    public function importExport()
    {
        $schemes = ThesaurusScheme::all();
        $recentImports = DB::table('thesaurus_imports')
                           ->orderBy('created_at', 'desc')
                           ->limit(10)
                           ->get();

        return view('thesaurus.tool.import-export', compact('schemes', 'recentImports'));
    }

    /**
     * Exporter un schéma en format SKOS RDF
     */
    public function exportScheme(Request $request)
    {
        $request->validate([
            'scheme_id' => 'required|exists:thesaurus_schemes,id',
            'format' => 'required|in:skos,rdf,csv,json',
            'include_relations' => 'boolean',
            'language' => 'nullable|string',
        ]);

        $scheme = ThesaurusScheme::with([
            'concepts.labels',
            'concepts.notes',
            'concepts.sourceRelations',
            'concepts.targetRelations',
            'organizations'
        ])->findOrFail($request->scheme_id);

        $format = $request->format;
        $includeRelations = $request->boolean('include_relations', true);
        $language = $request->language ?: 'fr-fr';

        switch ($format) {
            case 'skos':
                return $this->exportToSkos($scheme, $includeRelations, $language);
            case 'rdf':
                return $this->exportToRdf($scheme, $includeRelations, $language);
            case 'csv':
                return $this->exportToCsv($scheme, $includeRelations, $language);
            case 'json':
                return $this->exportToJson($scheme, $includeRelations, $language);
            default:
                return redirect()->back()->with('error', 'Format d\'export non supporté.');
        }
    }

    /**
     * Importer un fichier de thésaurus
     */
    public function importFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xml,rdf,csv,json|max:10240',
            'scheme_id' => 'nullable|exists:thesaurus_schemes,id',
            'format' => 'required|in:skos,rdf,csv,json',
            'language' => 'nullable|string',
            'merge_mode' => 'required|in:replace,merge,append',
        ]);

        $file = $request->file('file');
        $format = $request->format;
        $schemeId = $request->scheme_id;
        $language = $request->language ?: 'fr-fr';
        $mergeMode = $request->merge_mode;

        // Générer un ID unique pour cet import
        $importId = Str::uuid();

        // Enregistrer l'import
        DB::table('thesaurus_imports')->insert([
            'id' => $importId,
            'type' => $format,
            'filename' => $file->getClientOriginalName(),
            'status' => 'processing',
            'message' => 'Import en cours...',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            // Stocker le fichier temporairement
            $path = $file->storeAs('temp', $importId . '.' . $file->getClientOriginalExtension());

            $result = match ($format) {
                'skos' => $this->importFromSkos($path, $schemeId, $language, $mergeMode),
                'rdf' => $this->importFromRdf($path, $schemeId, $language, $mergeMode),
                'csv' => $this->importFromCsv($path, $schemeId, $language, $mergeMode),
                'json' => $this->importFromJson($path, $schemeId, $language, $mergeMode),
                default => throw new \Exception('Format d\'import non supporté')
            };

            // Mettre à jour le statut de l'import
            DB::table('thesaurus_imports')
                ->where('id', $importId)
                ->update([
                    'status' => 'completed',
                    'total_items' => $result['total'],
                    'processed_items' => $result['processed'],
                    'created_items' => $result['created'],
                    'updated_items' => $result['updated'],
                    'error_items' => $result['errors'],
                    'relationships_created' => $result['relationships'] ?? 0,
                    'message' => $result['message'],
                    'updated_at' => now(),
                ]);

            // Supprimer le fichier temporaire
            Storage::delete($path);

            return redirect()->back()->with('success', $result['message']);

        } catch (\Exception $e) {
            // Mettre à jour le statut d'erreur
            DB::table('thesaurus_imports')
                ->where('id', $importId)
                ->update([
                    'status' => 'failed',
                    'message' => $e->getMessage(),
                    'updated_at' => now(),
                ]);

            Log::error('Erreur lors de l\'import du thésaurus: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Erreur lors de l\'import: ' . $e->getMessage());
        }
    }

    /**
     * API: Obtenir tous les schémas thésaurus
     */
    public function apiSchemes()
    {
        $schemes = ThesaurusScheme::select('id', 'title', 'description', 'language', 'created_at')
                                 ->get();

        return response()->json($schemes);
    }

    /**
     * API: Obtenir tous les concepts avec pagination et filtres
     */
    public function apiConcepts(Request $request)
    {
        $query = ThesaurusConcept::with(['scheme:id,title', 'labels']);

        // Filtre par schéma
        if ($request->has('scheme_id') && $request->scheme_id) {
            $query->where('scheme_id', $request->scheme_id);
        }

        // Recherche textuelle
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pref_label', 'like', "%{$search}%")
                  ->orWhere('alt_labels', 'like', "%{$search}%")
                  ->orWhere('definition', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $concepts = $query->orderBy('pref_label')->paginate($perPage);

        return response()->json($concepts);
    }

    /**
     * API: Obtenir les concepts d'un schéma spécifique
     */
    public function apiSchemesConcepts(Request $request, $schemeId)
    {
        $query = ThesaurusConcept::with(['labels'])
                                ->where('scheme_id', $schemeId);

        // Recherche textuelle
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pref_label', 'like', "%{$search}%")
                  ->orWhere('alt_labels', 'like', "%{$search}%")
                  ->orWhere('definition', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $concepts = $query->orderBy('pref_label')->paginate($perPage);

        return response()->json($concepts);
    }

    /**
     * API: Obtenir les termes associés à un record
     */
    public function apiRecordTerms($recordId)
    {
        $record = Record::with(['thesaurusConcepts.scheme'])->findOrFail($recordId);

        return response()->json($record->thesaurusConcepts);
    }

    /**
     * API: Associer des termes à un record
     */
    public function apiAssociateTerms(Request $request, $recordId)
    {
        $request->validate([
            'concept_ids' => 'required|array',
            'concept_ids.*' => 'exists:thesaurus_concepts,id'
        ]);

        $record = Record::findOrFail($recordId);

        // Synchroniser les concepts (remplace les existants)
        $record->thesaurusConcepts()->sync($request->concept_ids);

        return response()->json([
            'message' => 'Termes associés avec succès',
            'count' => count($request->concept_ids)
        ]);
    }

    /**
     * API: Dissocier un terme d'un record
     */
    public function apiDisassociateTerm($recordId, $conceptId)
    {
        $record = Record::findOrFail($recordId);
        $record->thesaurusConcepts()->detach($conceptId);

        return response()->json([
            'message' => 'Terme dissocié avec succès'
        ]);
    }

    /**
     * API: Autocomplétion des concepts avec gestion des termes spécifiques
     */
    public function apiConceptsAutocomplete(Request $request)
    {
        $request->validate([
            'search' => 'required|string|min:3',
            'limit' => 'nullable|integer|min:1|max:10',
            'scheme_id' => 'nullable|exists:thesaurus_schemes,id'
        ]);

        $search = $request->search;
        $limit = $request->get('limit', 5);
        $schemeId = $request->scheme_id;

        $query = ThesaurusConcept::with(['scheme:id,title']);

        // Filtre par schéma si spécifié
        if ($schemeId) {
            $query->where('scheme_id', $schemeId);
        }

        // Recherche textuelle dans les labels préférés et alternatifs
        $query->where(function($q) use ($search) {
            $q->whereHas('labels', function($labelQuery) use ($search) {
                $labelQuery->where('label_value', 'like', "%{$search}%");
            });
        });

        $concepts = $query->orderBy('id')->limit($limit)->get();

        // Transformer les résultats pour inclure les termes spécifiques
        $results = $concepts->map(function ($concept) {
            $result = [
                'id' => $concept->id,
                'scheme_id' => $concept->scheme_id,
                'uri' => $concept->uri,
                'notation' => $concept->notation,
                'pref_label' => $concept->pref_label,
                'alt_labels' => $concept->alt_labels,
                'definition' => $concept->definition,
                'scheme' => $concept->scheme ? [
                    'id' => $concept->scheme->id,
                    'title' => $concept->scheme->title
                ] : null
            ];

            // Si c'est un terme générique, chercher un terme spécifique lié
            $specificTerms = $concept->narrowerConcepts()->with('scheme:id,title')->first();

            if ($specificTerms) {
                $result['specific_term'] = [
                    'id' => $specificTerms->id,
                    'scheme_id' => $specificTerms->scheme_id,
                    'uri' => $specificTerms->uri,
                    'notation' => $specificTerms->notation,
                    'pref_label' => $specificTerms->pref_label,
                    'alt_labels' => $specificTerms->alt_labels,
                    'definition' => $specificTerms->definition,
                    'scheme' => $specificTerms->scheme ? [
                        'id' => $specificTerms->scheme->id,
                        'title' => $specificTerms->scheme->title
                    ] : null
                ];
            }

            return $result;
        });

        return response()->json($results);
    }

    /**
     * Autocomplete search for concepts (web interface)
     */
    public function autocomplete(Request $request)
    {
        $query = $request->get('q', '');
        $schemeId = $request->get('scheme_id');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $conceptsQuery = ThesaurusConcept::with(['labels', 'scheme'])
            ->whereHas('labels', function($q) use ($query) {
                $q->where('value', 'LIKE', "%{$query}%");
            });

        if ($schemeId) {
            $conceptsQuery->where('scheme_id', $schemeId);
        }

        $concepts = $conceptsQuery->limit(20)->get();

        $results = $concepts->map(function($concept) {
            $preferredLabel = $concept->labels->where('type', 'preferred')->first();
            return [
                'id' => $concept->id,
                'text' => $preferredLabel ? $preferredLabel->value : 'No label',
                'scheme' => $concept->scheme ? $concept->scheme->title : null
            ];
        });

        return response()->json($results);
    }

    /**
     * Display a listing of concepts
     */
    public function concepts(Request $request)
    {
        $query = ThesaurusConcept::with(['labels', 'scheme', 'notes']);

        // Filter by scheme if provided
        if ($request->has('scheme_id') && $request->scheme_id) {
            $query->where('scheme_id', $request->scheme_id);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('labels', function($q) use ($search) {
                $q->where('value', 'LIKE', "%{$search}%");
            });
        }

        $concepts = $query->paginate(20);
        $schemes = ThesaurusScheme::all();

        return view('thesaurus.terms.search', compact('concepts', 'schemes'));
    }

    /**
     * Display the specified concept
     */
    public function showConcept(ThesaurusConcept $concept)
    {
        $concept->load([
            'labels',
            'notes',
            'scheme',
            'sourceRelations.targetConcept.labels',
            'targetRelations.sourceConcept.labels',
            'records'
        ]);

        return view('thesaurus.terms.show', compact('concept'));
    }

    /**
     * Display hierarchical view of concepts
     */
    public function hierarchy(Request $request)
    {
        $schemeId = $request->get('scheme_id');

        if ($schemeId) {
            $scheme = ThesaurusScheme::with([
                'concepts' => function($query) {
                    $query->with(['labels', 'sourceRelations.targetConcept.labels', 'targetRelations.sourceConcept.labels']);
                }
            ])->findOrFail($schemeId);

            // Build hierarchy tree
            $hierarchyData = $this->buildHierarchyTree($scheme->concepts);
        } else {
            $scheme = null;
            $hierarchyData = [];
        }

        $schemes = ThesaurusScheme::all();

        return view('thesaurus.terms.hierarchy', compact('scheme', 'hierarchyData', 'schemes'));
    }

    /**
     * Display record-concept relations management
     */
    public function recordConceptRelations(Request $request)
    {
        $schemeId = $request->get('scheme_id');
        $conceptId = $request->get('concept_id');

        $schemes = ThesaurusScheme::all();
        $records = collect();
        $concept = null;

        if ($conceptId) {
            $concept = ThesaurusConcept::with(['labels', 'scheme', 'records'])->findOrFail($conceptId);
            $records = $concept->records()->paginate(20);
        } elseif ($schemeId) {
            $records = Record::whereHas('thesaurusConcepts', function($query) use ($schemeId) {
                $query->where('scheme_id', $schemeId);
            })->with('thesaurusConcepts')->paginate(20);
        }

        return view('thesaurus.tool.record-concept-relations', compact('schemes', 'records', 'concept'));
    }

    /**
     * Display thesaurus statistics
     */
    public function statistics(Request $request)
    {
        $schemeId = $request->get('scheme_id');

        $stats = [
            'total_schemes' => DB::table('concept_schemes')->count(),
            'total_concepts' => DB::table('concepts')->count(),
            'total_labels' => DB::table('xl_labels')->count() + DB::table('alternative_labels')->count(),
            'total_relations' => DB::table('hierarchical_relations')->count() +
                              DB::table('associative_relations')->count() +
                              DB::table('mapping_relations')->count(),
            'records_with_concepts' => DB::table('record_thesaurus_concept')
                                    ->select('record_id')
                                    ->distinct()
                                    ->count(),
            'notes' => DB::table('thesaurus_concept_notes')->count(), // Compte réel des notes
            'organizations' => DB::table('thesaurus_organizations')->count(), // Compte réel des organisations
            'total_record_concept_relations' => DB::table('record_thesaurus_concept')->count(),
        ];

        if ($schemeId) {
            $scheme = ThesaurusScheme::findOrFail($schemeId);
            $stats['scheme_concepts'] = $scheme->concepts()->count();
            $stats['scheme_labels'] = ThesaurusLabel::whereHas('concept', function($query) use ($schemeId) {
                $query->where('scheme_id', $schemeId);
            })->count();
            $stats['scheme_relations'] = ThesaurusConceptRelation::whereHas('sourceConcept', function($query) use ($schemeId) {
                $query->where('scheme_id', $schemeId);
            })->count();
            $stats['scheme_notes'] = ThesaurusConceptNote::whereHas('concept', function($query) use ($schemeId) {
                $query->where('scheme_id', $schemeId);
            })->count();
            $stats['scheme_organizations'] = $scheme->organizations()->count();
        } else {
            $scheme = null;
        }

        $schemes = ThesaurusScheme::all();

        // Statistiques par schéma
        $schemeStats = ThesaurusScheme::select(['id', 'title', 'identifier']) // Ajout des champs nécessaires
            ->withCount([
            'concepts',
            'concepts as top_concepts_count' => function($query) {
                $query->whereDoesntHave('sourceRelations', function($q) {
                    $q->where('relation_type', 'broader');
                });
            }
        ])->get();

        // Statistiques par type de relation
        // Utiliser l'union des différentes tables de relations avec des noms compatibles avec la vue
        $relationStats = DB::table('hierarchical_relations')
            ->select(DB::raw("'broader' as relation_type, COUNT(*) as count")) // "broader" au lieu de "hierarchical"
            ->union(
                DB::table('associative_relations')
                ->select(DB::raw("'related' as relation_type, COUNT(*) as count")) // "related" au lieu de "associative"
            )
            ->union(
                DB::table('mapping_relations')
                ->select(DB::raw("'exactMatch' as relation_type, COUNT(*) as count")) // "exactMatch" au lieu de "mapping"
            )
            ->orderByDesc('count')
            ->get();

        // Statistiques par type de label
        $labelStats = DB::table('xl_labels')
            ->select('label_type', DB::raw('count(*) as count'))
            ->groupBy('label_type')
            ->orderByDesc('count')
            ->get();

        // Top 10 des records les plus associés à des concepts
        $topRecords = Record::select('id', 'name', 'code')
            ->withCount('thesaurusConcepts as thesaurus_concepts_count') // Utilise le nom correct pour la vue
            ->has('thesaurusConcepts')
            ->orderByDesc('thesaurus_concepts_count')
            ->limit(10)
            ->get();

        // Top 10 des concepts les plus utilisés
        $topConcepts = ThesaurusConcept::with(['labels', 'scheme']) // Chargement des relations nécessaires
            ->select('thesaurus_concepts.*', DB::raw('COUNT(record_thesaurus_concept.record_id) as records_count'))
            ->join('record_thesaurus_concept', 'thesaurus_concepts.id', '=', 'record_thesaurus_concept.concept_id')
            ->groupBy('thesaurus_concepts.id')
            ->orderByDesc('records_count')
            ->limit(10)
            ->get();

        return view('thesaurus.tool.statistics', compact(
            'stats', 'schemes', 'scheme', 'schemeStats', 'relationStats',
            'labelStats', 'topRecords', 'topConcepts'
        ));
    }

    /**
     * Auto associate concepts to records
     */
    public function autoAssociateConcepts(Request $request)
    {
        $request->validate([
            'scheme_id' => 'required|exists:thesaurus_schemes,id',
            'record_ids' => 'nullable|array',
            'record_ids.*' => 'exists:records,id',
            'min_weight' => 'nullable|numeric|min:0|max:1',
            'max_concepts' => 'nullable|integer|min:1|max:10',
        ]);

        $schemeId = $request->scheme_id;
        $recordIds = $request->record_ids ?? [];
        $minWeight = $request->get('min_weight', 0.5);
        $maxConcepts = $request->get('max_concepts', 5);

        $processed = 0;
        $associated = 0;

        if (empty($recordIds)) {
            // Process all records without concepts
            $records = Record::doesntHave('thesaurusConcepts')->get();
        } else {
            $records = Record::whereIn('id', $recordIds)->get();
        }

        foreach ($records as $record) {
            $concepts = $this->extractConceptsFromRecord($record, $schemeId, $minWeight, $maxConcepts);
            if (!empty($concepts)) {
                $record->thesaurusConcepts()->syncWithoutDetaching($concepts);
                $associated += count($concepts);
            }
            $processed++;
        }

        return redirect()->back()->with('success', "Traité {$processed} records, associé {$associated} concepts.");
    }

    /**
     * Display hierarchical relations for a term
     */
    public function hierarchicalRelationsIndex(ThesaurusConcept $term)
    {
        $term->load([
            'broaderConcepts',
            'narrowerConcepts',
            'scheme'
        ]);

        return view('thesaurus.hierarchical_relations.index', compact('term'));
    }

    /**
     * Show form to create broader relation
     */
    public function createBroaderRelation(ThesaurusConcept $term)
    {
        $availableTerms = ThesaurusConcept::where('scheme_id', $term->scheme_id)
                                         ->where('id', '!=', $term->id)
                                         ->get();

        return view('thesaurus.hierarchical_relations.create_broader', compact('term', 'availableTerms'));
    }

    /**
     * Store broader relation
     */
    public function storeBroaderRelation(Request $request, ThesaurusConcept $term)
    {
        $request->validate([
            'broader_term_id' => 'required|exists:thesaurus_concepts,id'
        ]);

        $broaderTerm = ThesaurusConcept::findOrFail($request->broader_term_id);

        // Create the hierarchical relation
        ThesaurusConceptRelation::create([
            'source_concept_id' => $term->id,
            'target_concept_id' => $broaderTerm->id,
            'relation_type' => 'broader'
        ]);

        return redirect()->route('thesaurus.hierarchical_relations.index', $term->id)
                        ->with('success', 'Relation hiérarchique créée avec succès.');
    }

    /**
     * Show form to create narrower relation
     */
    public function createNarrowerRelation(ThesaurusConcept $term)
    {
        $availableTerms = ThesaurusConcept::where('scheme_id', $term->scheme_id)
                                         ->where('id', '!=', $term->id)
                                         ->get();

        return view('thesaurus.hierarchical_relations.create_narrower', compact('term', 'availableTerms'));
    }

    /**
     * Store narrower relation
     */
    public function storeNarrowerRelation(Request $request, ThesaurusConcept $term)
    {
        $request->validate([
            'narrower_term_id' => 'required|exists:thesaurus_concepts,id'
        ]);

        $narrowerTerm = ThesaurusConcept::findOrFail($request->narrower_term_id);

        // Create the hierarchical relation
        ThesaurusConceptRelation::create([
            'source_concept_id' => $narrowerTerm->id,
            'target_concept_id' => $term->id,
            'relation_type' => 'broader'
        ]);

        return redirect()->route('thesaurus.hierarchical_relations.index', $term->id)
                        ->with('success', 'Relation hiérarchique créée avec succès.');
    }

    /**
     * Destroy hierarchical relation
     */
    public function destroyHierarchicalRelation(ThesaurusConcept $term, $relationType, ThesaurusConcept $relatedTerm)
    {
        if ($relationType === 'broader') {
            ThesaurusConceptRelation::where('source_concept_id', $term->id)
                                   ->where('target_concept_id', $relatedTerm->id)
                                   ->where('relation_type', 'broader')
                                   ->delete();
        } elseif ($relationType === 'narrower') {
            ThesaurusConceptRelation::where('source_concept_id', $relatedTerm->id)
                                   ->where('target_concept_id', $term->id)
                                   ->where('relation_type', 'broader')
                                   ->delete();
        }

        return redirect()->route('thesaurus.hierarchical_relations.index', $term->id)
                        ->with('success', 'Relation hiérarchique supprimée avec succès.');
    }

    // Méthodes privées pour l'extraction de concepts et autres fonctionnalités...
    private function extractConceptsFromRecord(Record $record, $schemeId = null, $minWeight = 0.5, $maxConcepts = 5)
    {
        // Implémentation simplifiée
        return [];
    }

    private function calculateWeight($label, $text, $labelType)
    {
        // Implémentation simplifiée
        return 0.5;
    }

    private function exportToSkos($scheme, $includeRelations, $language)
    {
        return response('Export SKOS pas encore implémenté', 501);
    }

    private function exportToRdf($scheme, $includeRelations, $language)
    {
        return response('Export RDF pas encore implémenté', 501);
    }

    private function exportToCsv($scheme, $includeRelations, $language)
    {
        return response('Export CSV pas encore implémenté', 501);
    }

    private function exportToJson($scheme, $includeRelations, $language)
    {
        return response('Export JSON pas encore implémenté', 501);
    }

    private function importFromSkos($path, $schemeId, $language, $mergeMode)
    {
        return [
            'total' => 0,
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
            'message' => 'Import SKOS pas encore implémenté',
        ];
    }

    private function importFromRdf($path, $schemeId, $language, $mergeMode)
    {
        return [
            'total' => 0,
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
            'message' => 'Import RDF pas encore implémenté',
        ];
    }

    private function importFromCsv($path, $schemeId, $language, $mergeMode)
    {
        return [
            'total' => 0,
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
            'message' => 'Import CSV pas encore implémenté',
        ];
    }

    private function importFromJson($path, $schemeId, $language, $mergeMode)
    {
        return [
            'total' => 0,
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
            'message' => 'Import JSON pas encore implémenté',
        ];
    }

    private function buildHierarchyTree($concepts)
    {
        $tree = [];
        $conceptsById = $concepts->keyBy('id');

        // Find root concepts (concepts without broader relations)
        foreach ($concepts as $concept) {
            $hasBroader = $concept->sourceRelations()->where('relation_type', 'broader')->exists();
            if (!$hasBroader) {
                $tree[] = $this->buildConceptNode($concept, $conceptsById);
            }
        }

        return $tree;
    }

    private function buildConceptNode($concept, $conceptsById)
    {
        $node = [
            'concept' => $concept,
            'children' => []
        ];

        // Find narrower concepts
        $narrowerRelations = $concept->targetRelations()->where('relation_type', 'narrower')->get();
        foreach ($narrowerRelations as $relation) {
            if (isset($conceptsById[$relation->source_concept_id])) {
                $childConcept = $conceptsById[$relation->source_concept_id];
                $node['children'][] = $this->buildConceptNode($childConcept, $conceptsById);
            }
        }

        return $node;
    }
}
