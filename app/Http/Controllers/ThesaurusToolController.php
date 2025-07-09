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

class ThesaurusToolController extends Controller
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
     * Associer automatiquement des concepts aux records
     */
    public function autoAssociateConcepts(Request $request)
    {
        $request->validate([
            'record_ids' => 'nullable|array',
            'record_ids.*' => 'exists:records,id',
            'scheme_id' => 'nullable|exists:thesaurus_schemes,id',
            'min_weight' => 'nullable|numeric|min:0.1|max:1.0',
            'max_concepts' => 'nullable|integer|min:1|max:10',
            'overwrite' => 'boolean',
        ]);

        $recordIds = $request->record_ids;
        $schemeId = $request->scheme_id;
        $minWeight = $request->min_weight ?: 0.5;
        $maxConcepts = $request->max_concepts ?: 5;
        $overwrite = $request->boolean('overwrite', false);

        // Si aucun record spécifié, traiter tous les records
        if (!$recordIds) {
            $recordIds = Record::pluck('id')->toArray();
        }

        $stats = [
            'processed' => 0,
            'associated' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        DB::beginTransaction();

        try {
            foreach ($recordIds as $recordId) {
                $record = Record::find($recordId);
                if (!$record) {
                    $stats['errors']++;
                    continue;
                }

                // Vérifier si des concepts sont déjà associés
                if (!$overwrite && $record->thesaurusConcepts()->exists()) {
                    $stats['skipped']++;
                    continue;
                }

                // Extraire les concepts du contenu du record
                $concepts = $this->extractConceptsFromRecord($record, $schemeId, $minWeight, $maxConcepts);

                if ($overwrite) {
                    // Supprimer les associations existantes
                    $record->thesaurusConcepts()->detach();
                }

                // Associer les nouveaux concepts
                foreach ($concepts as $concept) {
                    $record->thesaurusConcepts()->attach($concept['id'], [
                        'weight' => $concept['weight'],
                        'context' => 'automatic',
                        'extraction_note' => $concept['note'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                if (count($concepts) > 0) {
                    $stats['associated']++;
                }
                $stats['processed']++;
            }

            DB::commit();

            $message = "Traitement terminé: {$stats['processed']} records traités, {$stats['associated']} avec nouveaux concepts, {$stats['skipped']} ignorés, {$stats['errors']} erreurs.";
            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'association automatique: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du traitement: ' . $e->getMessage());
        }
    }

    /**
     * Afficher les relations entre records et concepts
     */
    public function recordConceptRelations(Request $request)
    {
        $query = Record::with(['thesaurusConcepts.labels', 'thesaurusConcepts.scheme']);

        // Filtres
        if ($request->scheme_id) {
            $query->whereHas('thesaurusConcepts', function ($q) use ($request) {
                $q->where('scheme_id', $request->scheme_id);
            });
        }

        if ($request->min_weight) {
            $query->whereHas('thesaurusConcepts', function ($q) use ($request) {
                $q->wherePivot('weight', '>=', $request->min_weight);
            });
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('content', 'LIKE', "%{$search}%")
                  ->orWhereHas('thesaurusConcepts.labels', function ($q) use ($search) {
                      $q->where('literal_form', 'LIKE', "%{$search}%");
                  });
            });
        }

        $records = $query->paginate(20);
        $schemes = ThesaurusScheme::all();

        return view('thesaurus.tool.record-concept-relations', compact('records', 'schemes'));
    }

    /**
     * Statistiques détaillées du thésaurus
     */
    public function statistics()
    {
        $stats = [
            'schemes' => ThesaurusScheme::count(),
            'concepts' => ThesaurusConcept::count(),
            'labels' => ThesaurusLabel::count(),
            'relations' => ThesaurusConceptRelation::count(),
            'notes' => ThesaurusConceptNote::count(),
            'organizations' => ThesaurusOrganization::count(),
            'records_with_concepts' => Record::has('thesaurusConcepts')->count(),
            'total_record_concept_relations' => DB::table('record_thesaurus_concept')->count(),
        ];

        // Statistiques par schéma
        $schemeStats = ThesaurusScheme::withCount([
            'concepts',
            'topConcepts',
        ])->get();

        // Statistiques par type de relation
        $relationStats = ThesaurusConceptRelation::select('relation_type', DB::raw('count(*) as count'))
                                                 ->groupBy('relation_type')
                                                 ->get();

        // Statistiques par type de label
        $labelStats = ThesaurusLabel::select('label_type', DB::raw('count(*) as count'))
                                    ->groupBy('label_type')
                                    ->get();

        // Records les plus associés
        $topRecords = Record::withCount('thesaurusConcepts')
                           ->orderBy('thesaurus_concepts_count', 'desc')
                           ->limit(10)
                           ->get();

        // Concepts les plus utilisés
        $topConcepts = ThesaurusConcept::withCount('records')
                                      ->orderBy('records_count', 'desc')
                                      ->limit(10)
                                      ->with(['labels' => function ($query) {
                                          $query->where('label_type', 'prefLabel');
                                      }])
                                      ->get();

        return view('thesaurus.tool.statistics', compact(
            'stats',
            'schemeStats',
            'relationStats',
            'labelStats',
            'topRecords',
            'topConcepts'
        ));
    }

    /**
     * Extraire des concepts d'un record
     */
    private function extractConceptsFromRecord(Record $record, $schemeId = null, $minWeight = 0.5, $maxConcepts = 5)
    {
        $concepts = [];
        $text = $record->content . ' ' . $record->name . ' ' . $record->biographical_history;

        // Rechercher des correspondances avec les labels du thésaurus
        $query = ThesaurusLabel::with('concept')
                              ->where('status', 1)
                              ->where('literal_form', '!=', '');

        if ($schemeId) {
            $query->whereHas('concept', function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            });
        }

        $labels = $query->get();

        foreach ($labels as $label) {
            $literalForm = $label->literal_form;
            $pattern = '/\b' . preg_quote($literalForm, '/') . '\b/ui';
            
            if (preg_match($pattern, $text, $matches)) {
                $weight = $this->calculateWeight($literalForm, $text, $label->label_type);
                
                if ($weight >= $minWeight) {
                    $concepts[] = [
                        'id' => $label->concept_id,
                        'weight' => $weight,
                        'note' => "Trouvé via le label: '{$literalForm}' (type: {$label->label_type})",
                        'label' => $literalForm,
                    ];
                }
            }
        }

        // Trier par poids et limiter le nombre
        usort($concepts, function ($a, $b) {
            return $b['weight'] <=> $a['weight'];
        });

        return array_slice($concepts, 0, $maxConcepts);
    }

    /**
     * Calculer le poids d'un concept pour un record
     */
    private function calculateWeight($label, $text, $labelType)
    {
        $baseWeight = 0.5;
        
        // Bonus selon le type de label
        switch ($labelType) {
            case 'prefLabel':
                $baseWeight += 0.3;
                break;
            case 'altLabel':
                $baseWeight += 0.2;
                break;
            case 'hiddenLabel':
                $baseWeight += 0.1;
                break;
        }

        // Bonus selon la fréquence d'apparition
        $occurrences = substr_count(strtolower($text), strtolower($label));
        $baseWeight += min($occurrences * 0.1, 0.2);

        // Bonus selon la longueur du terme (termes plus longs = plus spécifiques)
        $wordCount = str_word_count($label);
        if ($wordCount > 1) {
            $baseWeight += min($wordCount * 0.05, 0.15);
        }

        return min($baseWeight, 1.0);
    }

    /**
     * Méthodes d'export privées
     */
    private function exportToSkos($scheme, $includeRelations, $language)
    {
        // Implémentation de l'export SKOS
        // Cette méthode devrait générer un fichier RDF/XML conforme SKOS
        return response('Export SKOS pas encore implémenté', 501);
    }

    private function exportToRdf($scheme, $includeRelations, $language)
    {
        // Implémentation de l'export RDF
        return response('Export RDF pas encore implémenté', 501);
    }

    private function exportToCsv($scheme, $includeRelations, $language)
    {
        // Implémentation de l'export CSV
        return response('Export CSV pas encore implémenté', 501);
    }

    private function exportToJson($scheme, $includeRelations, $language)
    {
        // Implémentation de l'export JSON
        return response('Export JSON pas encore implémenté', 501);
    }

    /**
     * Méthodes d'import privées
     */
    private function importFromSkos($path, $schemeId, $language, $mergeMode)
    {
        // Implémentation de l'import SKOS
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
        // Implémentation de l'import RDF
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
        // Implémentation de l'import CSV
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
        // Implémentation de l'import JSON
        return [
            'total' => 0,
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
            'message' => 'Import JSON pas encore implémenté',
        ];
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
}
