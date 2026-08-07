<?php

namespace App\Http\Controllers;

use App\Models\RecordPhysical;
use App\Models\ThesaurusConcept;
use App\Models\ThesaurusScheme;
use App\Models\ThesaurusLabel;
use App\Models\ThesaurusConceptNote;
use App\Models\ThesaurusConceptRelation;
use App\Models\ThesaurusOrganization;
use App\Models\ThesaurusNamespace;
use App\Exceptions\ThesaurusImportException;
use App\Exports\ThesaurusSkosExport;
use App\Exports\ThesaurusCsvExport;
use App\Exports\ThesaurusJsonExport;
use App\Imports\ThesaurusSkosImport;
use App\Imports\ThesaurusCsvImport;
use App\Imports\ThesaurusJsonImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\ThesaurusImport;

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
            'records_with_concepts' => RecordPhysical::has('thesaurusConcepts')->count(),
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
     * Stocker un nouveau schéma de thésaurus
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'identifier' => 'required|string|max:50|unique:thesaurus_schemes',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'language' => 'required|string|max:10',
                'namespace_uri' => 'nullable|url|max:255',
            ]);

            DB::beginTransaction();

            // Générer une URI unique basée sur l'identifiant
            $baseUri = config('app.url') . '/thesaurus/schemes/';
            $uri = $baseUri . Str::slug($validated['identifier']);

            // Créer le schéma
            $scheme = new ThesaurusScheme();
            $scheme->identifier = $validated['identifier'];
            $scheme->title = $validated['title'];
            $scheme->description = $validated['description'] ?? null;
            $scheme->language = $validated['language'];
            $scheme->uri = $uri;
            $scheme->save();

            // Créer un namespace si URI fourni
            if (!empty($validated['namespace_uri'])) {
                try {
                    $namespace = new ThesaurusNamespace();
                    $namespace->prefix = $validated['identifier'];
                    $namespace->namespace_uri = $validated['namespace_uri'];
                    $namespace->description = 'Namespace for ' . $validated['title'];
                    $namespace->save();

                    // Mise à jour du schéma avec l'ID du namespace
                    $scheme->namespace_id = $namespace->id;
                    $scheme->save();
                } catch (\Exception $e) {
                    Log::error('Erreur lors de la création du namespace: ' . $e->getMessage());
                    // On continue sans namespace
                }
            }

            DB::commit();

            return redirect()
                ->route('thesaurus.index')
                ->with('success', 'Schéma de thésaurus créé avec succès.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erreur lors de la création du schéma de thésaurus: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
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
     * Exporter un schéma en différents formats
     *
     * Les formats disponibles sont :
     * - skos-rdf : SKOS exprimé en RDF/XML (norme W3C pour les systèmes d'organisation des connaissances)
     * - csv : Format tableur simple pour l'interopérabilité avec des outils bureautiques
     * - json : Format structuré pour l'interopérabilité avec des applications web
     */
    public function exportScheme(Request $request)
    {
        $request->validate([
            'scheme_id' => 'required|exists:thesaurus_schemes,id',
            'format' => 'required|in:skos-rdf,csv,json',
            'include_relations' => 'boolean',
            'language' => 'nullable|string',
        ]);

        $schemeId = $request->input('scheme_id');
        $format = $request->input('format');
        $includeRelations = $request->boolean('include_relations', true);
        $language = $request->input('language', 'fr-fr');

        try {
            // Charger le schéma pour vérifier qu'il existe
            $scheme = ThesaurusScheme::findOrFail($schemeId);

            // Utiliser la méthode d'export appropriée selon le format
            switch ($format) {
                case 'skos-rdf':
                    return $this->exportToSkos($scheme, $includeRelations, $language);
                case 'csv':
                    return $this->exportToCsv($scheme, $includeRelations, $language);
                case 'json':
                    return $this->exportToJson($scheme, $includeRelations, $language);
                default:
                    return redirect()->back()->with('error', 'Format d\'export non supporté.');
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'export du thésaurus: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de l\'export: ' . $e->getMessage());
        }
    }

    /**
     * Importer un fichier de thésaurus
     *
     * Formats supportés :
     * - skos-rdf : Modèle SKOS exprimé en RDF/XML selon les normes W3C
     *              (http://www.w3.org/TR/skos-reference/)
     * - csv : Format tableur pour imports simples
     * - json : Format structuré pour l'interopérabilité
     */
    public function importFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xml,rdf,csv,json,ttl,n3|max:20720',
            'scheme_id' => 'nullable|exists:thesaurus_schemes,id',
            'format' => 'required|in:skos-rdf,csv,json',
            'language' => 'nullable|string',
            'merge_mode' => 'required|in:replace,merge,append',
        ]);

        $file = $request->file('file');
        $format = $request->input('format');
        $schemeId = $request->input('scheme_id');
        $language = $request->input('language', 'fr-fr');
        $mergeMode = $request->input('merge_mode');

        // Vérifier la cohérence entre le format sélectionné et l'extension du fichier
        $extension = strtolower($file->getClientOriginalExtension());
        if ($format === 'skos-rdf' && !in_array($extension, ['xml', 'rdf'])) {
            return response()->json([
                'success' => false,
                'message' => "Le format SKOS-RDF nécessite un fichier avec extension .xml ou .rdf"
            ], 400);
        }
        if ($format === 'csv' && $extension !== 'csv') {
            return response()->json([
                'success' => false,
                'message' => "Le format CSV nécessite un fichier avec extension .csv"
            ], 400);
        }
        if ($format === 'json' && $extension !== 'json') {
            return response()->json([
                'success' => false,
                'message' => "Le format JSON nécessite un fichier avec extension .json"
            ], 400);
        }

        // Générer un ID unique pour cet import
        $importId = Str::uuid();

        // Enregistrer l'import
        $importRecord = \App\Models\ThesaurusImport::create([
            'id' => $importId,
            'type' => $format,
            'filename' => $file->getClientOriginalName(),
            'status' => 'processing',
            'message' => 'Import en cours...',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            // Créer le répertoire s'il n'existe pas
            $directory = storage_path('app/imports/thesaurus');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // Stocker le fichier temporairement avec des droits explicites
            $path = $file->storeAs('imports/thesaurus', $importId . '.' . $file->getClientOriginalExtension(), 'local');
            $fullPath = storage_path('app/' . $path);

            // S'assurer que le fichier est accessible en lecture
            chmod($fullPath, 0644);

            // Vérification supplémentaire que le fichier a été correctement stocké
            if (!file_exists($fullPath) || !is_readable($fullPath)) {
                throw new \Exception("Le fichier n'a pas pu être stocké correctement ou n'est pas accessible en lecture.");
            }

            // Importer le fichier selon le format
            $result = match ($format) {
                'skos-rdf' => $this->importFromSkos($path, $schemeId, $language, $mergeMode),
                'csv' => $this->importFromCsv($path, $schemeId, $language, $mergeMode),
                'json' => $this->importFromJson($path, $schemeId, $language, $mergeMode),
                default => throw new \Exception('Format d\'import non supporté')
            };

            // Mettre à jour le statut de l'import
            $importRecord->update([
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

        } catch (ThesaurusImportException $e) {
            // Gérer les erreurs spécifiques à l'import
            Log::error('Erreur d\'import thésaurus: ' . $e->getMessage() . ' (Code: ' . $e->getCode() . ')');

            // Mettre à jour le statut de l'import
            $importRecord->update([
                'status' => 'error',
                'message' => 'Erreur: ' . $e->getMessage(),
                'updated_at' => now(),
            ]);

            // Nettoyer les fichiers temporaires si nécessaire
            if (isset($path)) {
                Storage::delete($path);
            }

            return redirect()->back()->with('error', 'Erreur lors de l\'import: ' . $e->getMessage());

        } catch (\Exception $e) {
            // Gérer les erreurs génériques
            Log::error('Exception lors de l\'import thésaurus: ' . $e->getMessage());

            // Mettre à jour le statut de l'import
            $importRecord->update([
                'status' => 'error',
                'message' => 'Exception: ' . $e->getMessage(),
                'updated_at' => now(),
            ]);

            // Nettoyer les fichiers temporaires si nécessaire
            if (isset($path)) {
                Storage::delete($path);
            }

            return redirect()->back()->with('error', 'Une erreur est survenue: ' . $e->getMessage());
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
                $q->whereHas('labels', function($labelQuery) use ($search) {
                    $labelQuery->where('literal_form', 'like', "%{$search}%");
                })
                ->orWhereHas('notes', function($noteQuery) use ($search) {
                    $noteQuery->where('content', 'like', "%{$search}%");
                });
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $concepts = $query->orderBy('id')->paginate($perPage);

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
                $q->whereHas('labels', function($labelQuery) use ($search) {
                    $labelQuery->where('literal_form', 'like', "%{$search}%");
                })
                ->orWhereHas('notes', function($noteQuery) use ($search) {
                    $noteQuery->where('content', 'like', "%{$search}%");
                });
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $concepts = $query->orderBy('id')->paginate($perPage);

        return response()->json($concepts);
    }

    /**
     * API: Obtenir les termes associés à un record
     */
    public function apiRecordTerms($recordId)
    {
        $record = RecordPhysical::with(['thesaurusConcepts.scheme'])->findOrFail($recordId);

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

        $record = RecordPhysical::findOrFail($recordId);

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
        $record = RecordPhysical::findOrFail($recordId);
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
        try {
            $request->validate([
                'search' => 'required|string|min:2',
                'limit' => 'nullable|integer|min:1|max:10',
                'scheme_id' => 'nullable|exists:thesaurus_schemes,id'
            ]);

            $search = $request->search;
            $limit = $request->get('limit', 5);
            $schemeId = $request->scheme_id;

            // Log pour le débogage
            \Illuminate\Support\Facades\Log::info("Recherche thésaurus: {$search}, limit: {$limit}, scheme_id: {$schemeId}");

            $query = ThesaurusConcept::with(['scheme:id,title', 'labels']);

            // Filtre par schéma si spécifié
            if ($schemeId) {
                $query->where('scheme_id', $schemeId);
            }

            // Recherche textuelle dans les labels préférés et alternatifs
            $query->where(function($q) use ($search) {
                $q->whereHas('labels', function($labelQuery) use ($search) {
                    $labelQuery->where('literal_form', 'like', "%{$search}%");
                    \Illuminate\Support\Facades\Log::info("SQL search: literal_form LIKE %{$search}%");
                });
            });

            $concepts = $query->orderBy('id')->limit($limit)->get();

            // Transformer les résultats pour inclure les termes spécifiques
            $results = $concepts->map(function ($concept) {
                try {
                    // Obtenir le label préféré
                    $prefLabel = $concept->getPreferredLabel();
                    // Obtenir les labels alternatifs
                    $altLabels = $concept->getAlternativeLabels();
                    $altLabelsStr = $altLabels->pluck('literal_form')->implode(', ');

                    $result = [
                        'id' => $concept->id,
                        'scheme_id' => $concept->scheme_id,
                        'uri' => $concept->uri,
                        'notation' => $concept->notation,
                        'pref_label' => $prefLabel ? $prefLabel->literal_form : $concept->uri,
                        'alt_labels' => $altLabelsStr,
                        'definition' => $concept->notes()->where('type', 'definition')->first()->content ?? null,
                        'scheme' => $concept->scheme ? [
                            'id' => $concept->scheme->id,
                            'title' => $concept->scheme->title
                        ] : null
                    ];

                    // Si c'est un terme générique, chercher un terme spécifique lié
                    $specificTerms = $concept->narrowerConcepts()->with('scheme:id,title')->first();

                    if ($specificTerms) {
                        // Obtenir le label préféré pour le terme spécifique
                        $specificPrefLabel = $specificTerms->getPreferredLabel();
                        // Obtenir les labels alternatifs pour le terme spécifique
                        $specificAltLabels = $specificTerms->getAlternativeLabels();
                        $specificAltLabelsStr = $specificAltLabels->pluck('literal_form')->implode(', ');

                        $result['specific_term'] = [
                            'id' => $specificTerms->id,
                            'scheme_id' => $specificTerms->scheme_id,
                            'uri' => $specificTerms->uri,
                            'notation' => $specificTerms->notation,
                            'pref_label' => $specificPrefLabel ? $specificPrefLabel->literal_form : $specificTerms->uri,
                            'alt_labels' => $specificAltLabelsStr,
                            'definition' => $specificTerms->notes()->where('type', 'definition')->first()->content ?? null,
                            'scheme' => $specificTerms->scheme ? [
                                'id' => $specificTerms->scheme->id,
                                'title' => $specificTerms->scheme->title
                            ] : null
                        ];
                    }

                    return $result;
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Erreur lors du traitement du concept {$concept->id}: " . $e->getMessage());
                    return [
                        'id' => $concept->id,
                        'scheme_id' => $concept->scheme_id,
                        'uri' => $concept->uri,
                        'notation' => $concept->notation,
                        'pref_label' => $concept->uri,
                        'alt_labels' => '',
                        'error' => 'Erreur lors du traitement du concept'
                    ];
                }
            });

            return response()->json($results);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erreur lors de la recherche dans le thésaurus: " . $e->getMessage());
            return response()->json([
                'error' => 'Une erreur est survenue lors de la recherche',
                'message' => $e->getMessage()
            ], 500);
        }
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
                $q->where('literal_form', 'LIKE', "%{$query}%");
            });

        if ($schemeId) {
            $conceptsQuery->where('scheme_id', $schemeId);
        }

        $concepts = $conceptsQuery->limit(20)->get();

        $results = $concepts->map(function($concept) {
            $preferredLabel = $concept->labels->where('type', 'prefLabel')->first();
            return [
                'id' => $concept->id,
                'text' => $preferredLabel ? $preferredLabel->literal_form : 'No label',
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
                $q->where('literal_form', 'LIKE', "%{$search}%");
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
            $records = RecordPhysical::whereHas('thesaurusConcepts', function($query) use ($schemeId) {
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
        $topRecords = RecordPhysical::select('id', 'name', 'code')
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
            $records = RecordPhysical::doesntHave('thesaurusConcepts')->get();
        } else {
            $records = RecordPhysical::whereIn('id', $recordIds)->get();
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
    private function extractConceptsFromRecord(RecordPhysical $record, $schemeId = null, $minWeight = 0.5, $maxConcepts = 5)
    {
        // Implémentation simplifiée
        return [];
    }

    private function calculateWeight($label, $text, $labelType)
    {
        // Implémentation simplifiée
        return 0.5;
    }

    /**
     * Exporte un schéma de thésaurus au format SKOS-RDF (XML)
     *
     * SKOS est un modèle de données W3C pour les systèmes d'organisation des connaissances,
     * exprimé en RDF. Ce n'est pas un format distinct de RDF, mais un vocabulaire spécifique
     * défini avec RDF comme "grammaire" sous-jacente.
     *
     * @param ThesaurusScheme $scheme Le schéma à exporter
     * @param bool $includeRelations Inclure les relations entre concepts
     * @param string $language Langue des labels à inclure prioritairement
     * @return \Illuminate\Http\Response
     */
    private function exportToSkos($scheme, $includeRelations, $language)
    {
        $exporter = new ThesaurusSkosExport();
        $content = $exporter->export($scheme->getKey(), $language, $includeRelations);

        $fileName = 'thesaurus_export_skos-rdf_' . $scheme->identifier . '_' . date('YmdHis') . '.rdf';

        return response($content)
            ->header('Content-Type', 'application/rdf+xml')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Exporte un schéma de thésaurus au format CSV
     *
     * Format simple et tabulaire pour l'interopérabilité avec des tableurs
     * et des outils bureautiques.
     *
     * @param ThesaurusScheme $scheme Le schéma à exporter
     * @param bool $includeRelations Inclure les relations entre concepts
     * @param string $language Langue des labels à inclure prioritairement
     * @return \Illuminate\Http\Response
     */
    private function exportToCsv($scheme, $includeRelations, $language)
    {
        $exporter = new ThesaurusCsvExport();
        $content = $exporter->export($scheme->getKey(), $language, $includeRelations);

        $fileName = 'thesaurus_export_csv_' . $scheme->identifier . '_' . date('YmdHis') . '.csv';

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Exporte un schéma de thésaurus au format JSON
     *
     * Format structuré pour l'interopérabilité avec des applications web
     * et des API.
     *
     * @param ThesaurusScheme $scheme Le schéma à exporter
     * @param bool $includeRelations Inclure les relations entre concepts
     * @param string $language Langue des labels à inclure prioritairement
     * @return \Illuminate\Http\Response
     */
    private function exportToJson($scheme, $includeRelations, $language)
    {
        $exporter = new ThesaurusJsonExport();
        $content = $exporter->export($scheme->getKey(), $language, $includeRelations);

        $fileName = 'thesaurus_export_json_' . $scheme->identifier . '_' . date('YmdHis') . '.json';

        return response($content)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Importe un fichier au format SKOS-RDF
     *
     * SKOS (Simple Knowledge Organization System) est un modèle de données W3C
     * pour représenter des thésaurus, taxonomies et autres systèmes d'organisation
     * des connaissances. SKOS est exprimé en RDF, ce n'est pas un format distinct.
     *
     * @param string $path Chemin du fichier à importer
     * @param int|null $schemeId ID du schéma existant ou null pour créer un nouveau
     * @param string $language Langue par défaut des labels
     * @param string $mergeMode Mode de fusion (replace, merge, append)
     * @return array Résultats de l'import
     */
    private function importFromSkos($path, $schemeId, $language, $mergeMode)
    {
        $importer = new ThesaurusSkosImport();
        return $importer->import($path, $schemeId, $language, $mergeMode);
    }

    /**
     * Importe un fichier au format CSV
     *
     * Format simple et tabulaire pour l'import de concepts de thésaurus
     *
     * @param string $path Chemin du fichier à importer
     * @param int|null $schemeId ID du schéma existant ou null pour créer un nouveau
     * @param string $language Langue par défaut des labels
     * @param string $mergeMode Mode de fusion (replace, merge, append)
     * @return array Résultats de l'import
     */
    private function importFromCsv($path, $schemeId, $language, $mergeMode)
    {
        $importer = new ThesaurusCsvImport();
        return $importer->import($path, $schemeId, $language, $mergeMode);
    }

    /**
     * Importe un fichier au format JSON
     *
     * Format structuré pour l'interopérabilité avec d'autres systèmes
     *
     * @param string $path Chemin du fichier à importer
     * @param int|null $schemeId ID du schéma existant ou null pour créer un nouveau
     * @param string $language Langue par défaut des labels
     * @param string $mergeMode Mode de fusion (replace, merge, append)
     * @return array Résultats de l'import
     */
    private function importFromJson($path, $schemeId, $language, $mergeMode)
    {
        $importer = new ThesaurusJsonImport();
        return $importer->import($path, $schemeId, $language, $mergeMode);
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

    /**
     * API: Recherche de concepts pour le module MCP
     */
    public function searchApi(Request $request)
    {
        try {
            $request->validate([
                'keywords' => 'required|array',
                'keywords.*' => 'string|min:2',
                'limit' => 'nullable|integer|min:1|max:50'
            ]);

            $keywords = $request->keywords;
            $limit = $request->get('limit', 10);

            $allConcepts = collect();

            foreach ($keywords as $keyword) {
                $query = ThesaurusConcept::with(['scheme:id,title', 'labels']);

                // Recherche textuelle dans les labels
                $query->where(function($q) use ($keyword) {
                    $q->whereHas('labels', function($labelQuery) use ($keyword) {
                        $labelQuery->where('literal_form', 'like', "%{$keyword}%");
                    });
                });

                $concepts = $query->limit($limit)->get();

                foreach ($concepts as $concept) {
                    $prefLabel = $concept->getPreferredLabel();

                    $allConcepts->push([
                        'id' => $concept->id,
                        'uri' => $concept->uri,
                        'notation' => $concept->notation,
                        'preferred_label' => $prefLabel ? $prefLabel->literal_form : $concept->uri,
                        'scheme_id' => $concept->scheme_id,
                        'scheme_title' => $concept->scheme ? $concept->scheme->title : null,
                        'matched_keyword' => $keyword
                    ]);
                }
            }

            // Supprimer les doublons basés sur l'ID
            $uniqueConcepts = $allConcepts->unique('id')->values();

            return response()->json([
                'success' => true,
                'concepts' => $uniqueConcepts,
                'total' => $uniqueConcepts->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la recherche de concepts:', [
                'error' => $e->getMessage(),
                'keywords' => $request->keywords ?? []
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la recherche',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
