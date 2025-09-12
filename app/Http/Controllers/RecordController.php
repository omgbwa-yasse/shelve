<?php

namespace App\Http\Controllers;

use App\Exports\RecordsExport;
use App\Imports\RecordsImport;
use App\Services\EADImportService;
use App\Services\SedaImportService;
use App\Services\AttachmentTextExtractor;
use AiBridge\Facades\AiBridge;
use App\Models\RecordAttachment;
use App\Models\SlipStatus;
use App\Models\Attachment;
use App\Models\Dolly;
use App\Models\Organisation;
use App\Models\Record;
use App\Models\RecordSupport;
use App\Models\RecordStatus;
use App\Models\Container;
use App\Models\Activity;
use App\Models\Slip;
use App\Models\ThesaurusConcept;
use App\Models\User;
use App\Models\Accession;
use App\Models\Author;
use App\Models\AuthorType;
use App\Models\RecordLevel;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use ZipArchive;

class RecordController extends Controller
{
    /**
     * Traite et nettoie les IDs reçus du formulaire
     *
     * @param mixed $ids
     * @return array
     */
    private function processIds($ids)
    {
        if (is_null($ids)) {
            return [];
        }

        // Si c'est une chaîne, la diviser par les virgules
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        // Si c'est un tableau avec une chaîne en premier élément, la diviser
        if (is_array($ids) && isset($ids[0]) && is_string($ids[0]) && strpos($ids[0], ',') !== false) {
            $ids = explode(',', $ids[0]);
        }

        // Si c'est déjà un tableau d'entiers ou de chaînes, on le traite directement
        if (is_array($ids)) {
            return array_filter(array_map('intval', $ids));
        }

        // Si ce n'est pas un tableau, retourner vide
        return [];
    }



    public function search(Request $request)
    {
        $query = $request->input('query');
        $keywordFilter = $request->input('keyword_filter');

        $recordsQuery = Record::with(['level', 'status', 'support', 'activity', 'containers', 'authors', 'thesaurusConcepts', 'attachments', 'keywords']);

        if (!empty($query)) {
            // Recherche dans l'intitulé (name) et le code
            $recordsQuery->where(function($q) use ($query) {
                $q->where('name', 'LIKE', '%' . $query . '%')
                    ->orWhere('code', 'LIKE', '%' . $query . '%');
            });
        }

        // Filtrage par mot-clé si fourni
        if (!empty($keywordFilter)) {
            $recordsQuery->whereHas('keywords', function ($q) use ($keywordFilter) {
                $q->where('name', 'LIKE', '%' . $keywordFilter . '%');
            });
        }

        // Si ni query ni keyword_filter ne sont fournis, retourner une collection vide paginée
        if (empty($query) && empty($keywordFilter)) {
            $records = Record::where('id', 0)->paginate(10);
        } else {
            $records = $recordsQuery->paginate(10);
        }

        // Charger les données nécessaires pour la vue index
        $slipStatuses = SlipStatus::all();
        $statuses = RecordStatus::all();
        $terms = [];
        $users = User::select('id', 'name')->get();
        $organisations = Organisation::select('id', 'name')->get();

        // Contexte de navigation pour une expérience fluide
        session([
            'records.back_url' => $request->fullUrl(),
            'records.list_ids' => $records->getCollection()->pluck('id')->all(),
        ]);

        return view('records.index', compact(
            'records',
            'statuses',
            'slipStatuses',
            'terms',
            'users',
            'organisations',
            'query'
        ));
    }

    public function index(Request $request)
    {
        Gate::authorize('records_view');

        // Initialiser la query des records avec les relations
        $query = Record::with([
            'level', 'status', 'support', 'activity', 'containers', 'authors', 'thesaurusConcepts', 'attachments', 'keywords'
        ]);

        // Filtrage par mot-clé si fourni
        if ($request->filled('keyword_filter')) {
            $keywordFilter = $request->input('keyword_filter');
            $query->whereHas('keywords', function ($q) use ($keywordFilter) {
                $q->where('name', 'LIKE', '%' . $keywordFilter . '%');
            });
        }

        // Pagination des résultats
        $records = $query->paginate(10);

        $slipStatuses = SlipStatus::all();
        $statuses = RecordStatus::all();
        $terms = [];
        $users = User::select('id', 'name')->get();
        $organisations = Organisation::select('id', 'name')->get();

        // Contexte de navigation pour une expérience fluide
        session([
            'records.back_url' => $request->fullUrl(),
            'records.list_ids' => $records->getCollection()->pluck('id')->all(),
        ]);

        return view('records.index', compact(
            'records',
            'statuses',
            'slipStatuses',
            'terms',
            'users',
            'organisations'
        ));
    }

    public function create()
    {
        Gate::authorize('records_create');

        $statuses = RecordStatus::all();
        $supports = RecordSupport::all();
        $activities = Activity::all();
        $parents = Record::all();
        $containers = Container::all();
        $users = User::all();
        $levels = RecordLevel::all();
        $records = Record::all();
        $authors = Author::with('authorType')->get();
        $terms = []; // Removed ThesaurusConcept::all() since we use AJAX
        $authorTypes = AuthorType::all();
        $parents = Author::all();
        return view('records.create', compact('authorTypes', 'parents','records','authors','levels','statuses', 'supports', 'activities', 'parents', 'containers', 'users', 'terms'));
    }

    public function createFull()
    {
        Gate::authorize('records_create');

        $statuses = RecordStatus::all();
        $supports = RecordSupport::all();
        $activities = Activity::all();
        $parents = Record::all();
        $containers = Container::all();
        $users = User::all();
        $levels = RecordLevel::all();
        $records = Record::all();
        $authors = Author::with('authorType')->get();
        $terms = []; // Removed ThesaurusConcept::all() since we use AJAX
        $authorTypes = AuthorType::all();
        $parents = Author::all();
        return view('records.createFull', compact('authorTypes', 'parents','records','authors','levels','statuses', 'supports', 'activities', 'parents', 'containers', 'users', 'terms'));
    }

    public function store(Request $request)
    {
        Gate::authorize('records_create');

        // Debug : log des données reçues
        Log::info('Store method called', [
            'request_all' => $request->all(),
            'code_value' => $request->input('code'),
            'name_value' => $request->input('name'),
            'user_id' => Auth::id(),
            'organisation_id' => Auth::user()->current_organisation_id
        ]);

        // Gestion des dates
        $dateFormat = 'Y'; // Format par défaut
        if ($request->filled('date_start') || $request->filled('date_end')) {
            $dateFormat = $this->getDateFormat($request->date_start, $request->date_end);
        }

        $request->merge([
            'date_format' => $dateFormat,
            'user_id' => Auth::id(),
            'organisation_id' => Auth::user()->current_organisation_id,
        ]);

        // Validation avec author_ids et term_ids optionnels
        $validatedData = $request->validate([
            'code' => 'required|string|max:10',
            'name' => 'required|string',
            'date_format' => 'required|string|max:1',
            'date_start' => 'nullable|string|max:10',
            'date_end' => 'nullable|string|max:10',
            'date_exact' => 'nullable|date',
            'level_id' => 'required|integer|exists:record_levels,id',
            'width' => 'nullable|numeric|between:0,99999999.99',
            'width_description' => 'nullable|string|max:100',
            'biographical_history' => 'nullable|string',
            'archival_history' => 'nullable|string',
            'acquisition_source' => 'nullable|string',
            'content' => 'nullable|string',
            'appraisal' => 'nullable|string',
            'accrual' => 'nullable|string',
            'arrangement' => 'nullable|string',
            'access_conditions' => 'nullable|string|max:50',
            'reproduction_conditions' => 'nullable|string|max:50',
            'language_material' => 'nullable|string|max:50',
            'characteristic' => 'nullable|string|max:100',
            'finding_aids' => 'nullable|string|max:100',
            'location_original' => 'nullable|string|max:100',
            'location_copy' => 'nullable|string|max:100',
            'related_unit' => 'nullable|string|max:100',
            'publication_note' => 'nullable|string',
            'note' => 'nullable|string',
            'archivist_note' => 'nullable|string',
            'rule_convention' => 'nullable|string|max:100',
            'created_at' => 'nullable|date',
            'updated_at' => 'nullable|date',
            'status_id' => 'required|integer|exists:record_statuses,id',
            'support_id' => 'required|integer|exists:record_supports,id',
            'activity_id' => 'required|integer|exists:activities,id',
            'parent_id' => 'nullable|integer|exists:records,id',
            'accession_id' => 'nullable|integer|exists:accessions,id',
            'user_id' => 'required|integer|exists:users,id',
            'container_ids' => 'nullable|array',
            'container_ids.*' => 'integer|exists:containers,id',
        ]);

        // Supprimer author_ids et term_ids des données validées car ils ne sont pas des champs de la table
        $recordData = $validatedData;

        try {
            $record = Record::create($recordData);
        } catch (\Exception $e) {
            Log::error('Error creating record: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'validated_data' => $validatedData,
                'stack_trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Erreur lors de la création du record: ' . $e->getMessage()])->withInput();
        }

        // Traitement des auteurs (obligatoire)
        $author_ids = $this->processIds($request->input('author_ids', []));



        if (empty($author_ids)) {
            $record->delete();
            return back()->withErrors(['author_ids' => 'Au moins un auteur doit être sélectionné.'])->withInput();
        }

        foreach ($author_ids as $author_id) {
            if ($author_id > 0) {
                $record->authors()->attach($author_id);
            }
        }

        // Traitement des termes du thésaurus (optionnel)
        $term_ids = $this->processIds($request->input('term_ids', []));

        foreach ($term_ids as $term_id) {
            if ($term_id > 0) {
                $record->thesaurusConcepts()->attach($term_id, [
                    'weight' => 1.0,
                    'context' => 'manuel',
                    'extraction_note' => null
                ]);
            }
        }

        // Containers (multiple via pivot)
        $containerIds = $this->processIds($request->input('container_ids', []));
        if (!empty($containerIds)) {
            $attachData = [];
            foreach (array_unique($containerIds) as $cid) {
                if ($cid > 0) {
                    $attachData[$cid] = [
                        'description' => null,
                        'creator_id' => Auth::id(),
                    ];
                }
            }
            if (!empty($attachData)) {
                $record->containers()->attach($attachData);
            }
        }

        // Traitement des mots-clés
        if ($request->filled('keywords')) {
            $keywords = \App\Models\Keyword::processKeywordsString($request->keywords);
            $record->keywords()->attach($keywords->pluck('id'));
        }

        $record->load([
            'children',
            'parent',
            'level',
            'status',
            'support',
            'activity',
            'containers',
            'recordContainers.container',
            'user'
        ]);

        // Log pour debug de la redirection
        Log::info('About to redirect after record creation', [
            'record_id' => $record->id,
            'route_url' => route('records.show', $record->id),
            'record_exists' => Record::where('id', $record->id)->exists()
        ]);

        return redirect()->route('records.show', $record->id)->with('success', 'Record created successfully.');
    }

    private function getDateFormat($dateStart, $dateEnd)
    {
        if (empty($dateStart) && empty($dateEnd)) {
            return 'Y';
        }

        if (empty($dateStart) || empty($dateEnd)) {
            return 'Y';
        }

        try {
            $start = new \DateTime($dateStart);
            $end = new \DateTime($dateEnd);

            if ($start->format('Y') !== $end->format('Y')) {
                return 'Y';
            } elseif ($start->format('m') !== $end->format('m')) {
                return 'M';
            } else {
                return 'D';
            }
        } catch (\Exception $e) {
            return 'Y'; // Format par défaut en cas d'erreur
        }
    }

    public function show(Record $record, Request $request)
    {

        Gate::authorize('records_view');

        $record->load([
            'children',
            'parent',
            'level',
            'status',
            'support',
            'activity',
            'containers',
            'recordContainers.container',
            'user'
        ]);

        // Calcul précédent/suivant basé sur la dernière liste consultée
        $listIds = (array) session('records.list_ids', []);
        $prevId = null;
        $nextId = null;
        if (!empty($listIds)) {
            $index = array_search($record->id, $listIds, true);
            if ($index !== false) {
                if ($index > 0) {
                    $prevId = $listIds[$index - 1];
                }
                if ($index < count($listIds) - 1) {
                    $nextId = $listIds[$index + 1];
                }
            }
        }

        return view('records.show', compact('record', 'prevId', 'nextId'));
    }

    public function showFull(Record $record, Request $request)
    {
        Gate::authorize('records_view');

        // Charger toutes les relations pour la vue détaillée
        $record->load([
            'children',
            'parent',
            'level',
            'status',
            'support',
            'activity',
            'authors',
            'containers',
            'recordContainers.container',
            'thesaurusConcepts',
            'attachments',
            'user',
            'organisation'
        ]);

        // Calcul précédent/suivant basé sur la dernière liste consultée
        $listIds = (array) session('records.list_ids', []);
        $prevId = null;
        $nextId = null;
        if (!empty($listIds)) {
            $index = array_search($record->id, $listIds, true);
            if ($index !== false) {
                if ($index > 0) {
                    $prevId = $listIds[$index - 1];
                }
                if ($index < count($listIds) - 1) {
                    $nextId = $listIds[$index + 1];
                }
            }
        }

        return view('records.showFull', compact('record', 'prevId', 'nextId'));
    }

    public function edit(Record $record, Request $request)
    {
        Gate::authorize('records_update');

        // Charger le record avec ses relations, y compris authorType pour les auteurs
        $record->load([
            'authors.authorType',
            'thesaurusConcepts'
        ]);

        $authors = Author::with('authorType')->get();
        $statuses = RecordStatus::all();
        $supports = RecordSupport::all();
        $activities = Activity::all();
        $parents = Record::all();
        $containers = Container::all();
        $users = User::all();
        $levels = RecordLevel::all();
        // Removed $terms = ThesaurusConcept::all(); since we use AJAX

        $author_ids = $record->authors->pluck('id')->toArray();
        $term_ids = $record->thesaurusConcepts->pluck('id')->toArray();

        // Vérifier si un titre suggéré est fourni via l'URL
        $suggestedTitle = $request->query('suggested_title');

        return view('records.edit', compact('levels', 'record', 'statuses', 'supports', 'activities',
            'parents', 'containers', 'users', 'authors', 'author_ids',
            'term_ids', 'suggestedTitle'));
    }

    public function update(Request $request, Record $record)
    {
        Gate::authorize('records_update');

        $request->merge(['date_format' => $request->input('date_format', 'Y')]);
        $request->merge(['user_id' => Auth::id()]);
        $validatedData = $request->validate([
            'code' => 'required|string|max:10',
            'name' => 'required|string',
            'date_format' => 'required|string|max:1',
            'date_start' => 'nullable|string|max:10',
            'date_end' => 'nullable|string|max:10',
            'date_exact' => 'nullable|date',
            'level_id' => 'required|integer|exists:record_levels,id',
            'width' => 'nullable|numeric|between:0,99999999.99',
            'width_description' => 'nullable|string|max:100',
            'biographical_history' => 'nullable|string',
            'archival_history' => 'nullable|string',
            'acquisition_source' => 'nullable|string',
            'content' => 'nullable|string',
            'appraisal' => 'nullable|string',
            'accrual' => 'nullable|string',
            'arrangement' => 'nullable|string',
            'access_conditions' => 'nullable|string|max:50',
            'reproduction_conditions' => 'nullable|string|max:50',
            'language_material' => 'nullable|string|max:50',
            'characteristic' => 'nullable|string|max:100',
            'finding_aids' => 'nullable|string|max:100',
            'location_original' => 'nullable|string|max:100',
            'location_copy' => 'nullable|string|max:100',
            'related_unit' => 'nullable|string|max:100',
            'publication_note' => 'nullable|string',
            'note' => 'nullable|string',
            'archivist_note' => 'nullable|string',
            'rule_convention' => 'nullable|string|max:100',
            'created_at' => 'nullable|date',
            'updated_at' => 'nullable|date',
            'status_id' => 'required|integer|exists:record_statuses,id',
            'support_id' => 'required|integer|exists:record_supports,id',
            'activity_id' => 'required|integer|exists:activities,id',
            'parent_id' => 'nullable|integer|exists:records,id',
            'accession_id' => 'nullable|integer|exists:accessions,id',
            'user_id' => 'required|integer|exists:users,id',
            'container_ids' => 'nullable|array',
            'container_ids.*' => 'integer|exists:containers,id',
        ]);

        // Supprimer author_ids et term_ids des données validées car ils ne sont pas des champs de la table
        unset($validatedData['author_ids'], $validatedData['term_ids']);

        // Mettez à jour l'enregistrement
        $record->update($validatedData);

        // Traitement des auteurs
        $author_ids = $this->processIds($request->input('author_ids', []));

        if (empty($author_ids)) {
            return back()->withErrors(['author_ids' => 'Au moins un auteur doit être sélectionné.'])->withInput();
        }

        // Mettez à jour les relations entre les auteurs et l'enregistrement
        $record->authors()->sync($author_ids);

        // Traitement des termes du thésaurus
        $term_ids = $this->processIds($request->input('term_ids', []));

        // Mettez à jour les relations entre les concepts du thésaurus et l'enregistrement
        if (!empty($term_ids)) {
            $conceptData = [];
            foreach ($term_ids as $conceptId) {
                $conceptData[$conceptId] = ['weight' => 1.0]; // Poids par défaut à 1.0
            }
            $record->thesaurusConcepts()->sync($conceptData);
        } else {
            $record->thesaurusConcepts()->detach();
        }

        // Update containers pivot
        $containerIds = $this->processIds($request->input('container_ids', []));
        if (!empty($containerIds)) {
            $syncData = [];
            foreach (array_unique($containerIds) as $cid) {
                if ($cid > 0) {
                    $syncData[$cid] = [
                        'description' => null,
                        'creator_id' => Auth::id(),
                    ];
                }
            }
            $record->containers()->sync($syncData);
        } else {
            $record->containers()->detach();
        }

        // Traitement des mots-clés
        if ($request->filled('keywords')) {
            $keywords = \App\Models\Keyword::processKeywordsString($request->keywords);
            $record->keywords()->sync($keywords->pluck('id'));
        } else {
            $record->keywords()->detach();
        }

        return redirect()->route('records.show', $record->id)->with('success', 'Record updated successfully.');
    }

    public function destroy(Record $record)
    {
        Gate::authorize('records_delete');

        $record->delete();

        return redirect()->route('records.index')->with('success', 'Record deleted successfully.');
    }

    // ici c\'est pour l'import export
    public function exportButton(Request $request)
    {
        // Vérifier les permissions d'export pour les records
        Gate::authorize('records_export');

        $recordIds = explode(',', $request->query('records'));
        $format = $request->query('format', 'excel');
        // Eager-load relations for richer exports (EAD/PDF)
        $records = Record::with([
            'level','status','support','activity','organisation',
            'containers','recordContainers.container','authors','thesaurusConcepts','attachments',
            'children'
        ])->whereIn('id', $recordIds)->get();

        $slips = "";
        try {
            switch ($format) {
                case 'excel':
                    return Excel::download(new RecordsExport($records), 'records_export.xlsx');
                case 'ead':
                    $ead = new \App\Exports\EADExport();
                    $xml = $ead->exportRecords($records);
                    return response($xml)
                        ->header('Content-Type', 'application/xml')
                        ->header('Content-Disposition', 'attachment; filename="records_export.xml"');
                case 'ead2002':
                    $ead2002 = new \App\Services\EAD2002ExportService();
                    $xml = $ead2002->exportRecords($records);
                    return response($xml)
                        ->header('Content-Type', 'application/xml')
                        ->header('Content-Disposition', 'attachment; filename="records_export_ead2002.xml"');
                case 'dublincore':
                    $dc = new \App\Services\DublinCoreExportService();
                    $xml = $dc->exportRecords($records);
                    return response($xml)
                        ->header('Content-Type', 'application/xml')
                        ->header('Content-Disposition', 'attachment; filename="records_export_dublincore.xml"');
                case 'seda':
                    return $this->exportSEDA($records,$slips);
                case 'pdf':
                    // Charger les relations nécessaires et conserver l'ordre de sélection
                    $recordsForPdf = Record::with([
                        'level','status','support','activity','containers','authors','thesaurusConcepts','attachments'
                    ])->whereIn('id', $recordIds)->get();

                    $idOrder = array_flip($recordIds);
                    $recordsForPdf = $recordsForPdf->sortBy(fn($r) => $idOrder[$r->id] ?? PHP_INT_MAX)->values();

                    $pdf = PDF::loadView('records.print', [
                        'records' => $recordsForPdf,
                    ]);

                    return $pdf->download('records_export.pdf');
                default:
                    return response()->json(['error' => 'Format d\'exportation non valide.'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'exportation: ' . $e->getMessage());
            return response()->json(['error' => 'Une erreur est survenue lors de l\'exportation.'], 500);
        }
    }

    public function export(Request $request)
    {
        Gate::authorize('records_export');

        $dollyId = $request->input('dolly_id');
        $format = $request->input('format');

        if ($dollyId) {
            $dolly = Dolly::findOrFail($dollyId);
            $records = $dolly->records()->with([
                'level','status','support','activity','organisation',
                'containers','recordContainers.container','authors','thesaurusConcepts','attachments','children'
            ])->get();
            $slips = $dolly->slips;
        } else {
            $records = Record::with([
                'level','status','support','activity','organisation',
                'containers','recordContainers.container','authors','thesaurusConcepts','attachments','children'
            ])->get();
            $slips = Slip::all();
        }

        switch ($format) {
            case 'excel':
                return Excel::download(new RecordsExport($records), 'records.xlsx');
            case 'ead':
                $ead = new \App\Exports\EADExport();
                $xml = $ead->exportRecords($records);
                return response($xml)
                    ->header('Content-Type', 'application/xml')
                    ->header('Content-Disposition', 'attachment; filename="records.xml"');
            case 'seda':
                return $this->exportSEDA($records, $slips);
            default:
                return redirect()->back()->with('error', 'Invalid export format');
        }
    }

    public function importForm()
    {
        Gate::authorize('records_import');

        return view('records.import');
    }

    public function exportForm()
    {
        Gate::authorize('records_export');

        $dollies = Dolly::all();
        return view('records.export', compact('dollies'));
    }

    public function import(Request $request)
    {
        Gate::authorize('records_import');

        // Import avec remapping (appel AJAX)
        if ($request->filled('mapping')) {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,csv',
                'format' => 'required|in:excel,csv',
                'mapping' => 'required|json',
            ]);

            try {
                $file = $request->file('file');
                $format = $request->input('format');
                $mapping = json_decode($request->input('mapping'), true);
                $hasHeaders = (bool) $request->input('has_headers', false);
                $updateExisting = (bool) $request->input('update_existing', false);
                $autoGenerateCodes = (bool) $request->input('auto_generate_codes', true);

                // Créer un nouveau Dolly pour cet import
                $dolly = Dolly::create([
                    'name' => 'Import ' . now()->format('Y-m-d H:i:s'),
                    'description' => 'Import automatique avec mapping personnalisé',
                    'category' => 'record',
                    'is_public' => false,
                    'created_by' => Auth::id(),
                    'owner_organisation_id' => Auth::user()->current_organisation_id,
                ]);

                // Lancer l'import
                $import = new RecordsImport($dolly, $mapping, $hasHeaders, $updateExisting, $autoGenerateCodes);
                Excel::import($import, $file);

                $summary = $import->getImportSummary();

                $message = "Import terminé avec succès. ";
                if ($summary['imported'] > 0) {
                    $message .= "{$summary['imported']} enregistrement(s) importé(s). ";
                }
                if ($summary['auto_generated_codes'] > 0) {
                    $message .= "{$summary['auto_generated_codes']} code(s) généré(s) automatiquement. ";
                }
                if ($summary['skipped'] > 0) {
                    $message .= "{$summary['skipped']} ligne(s) ignorée(s) (champs requis manquants). ";
                }
                if ($summary['errors'] > 0) {
                    $message .= "{$summary['errors']} erreur(s) rencontrée(s).";
                }

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'dolly_id' => $dolly->id,
                    'summary' => $summary,
                ]);
            } catch (\Exception $e) {
                Log::error('Erreur lors de l\'import remappé: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'import: ' . $e->getMessage(),
                ], 500);
            }
        }

        // Import classique (formulaire HTML existant)
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xml',
            'format' => 'required|in:excel,ead,seda',
        ]);

        $file = $request->file('file');
        $format = $request->input('format');

        // Créer un nouveau Dolly
        $dolly = Dolly::create([
            'name' => 'Import ' . now()->format('Y-m-d H:i:s'),
            'description' => 'Imported data',
            'category' => 'record',
            'is_public' => false,
            'created_by' => Auth::id(),
            'owner_organisation_id' => Auth::user()->current_organisation_id,
        ]);

        try {
            switch ($format) {
                case 'excel':
                    $import = new RecordsImport($dolly, [], true, false, true);
                    Excel::import($import, $file);
                    $summary = $import->getImportSummary();

                    $message = "Import terminé avec succès. ";
                    if ($summary['imported'] > 0) {
                        $message .= "{$summary['imported']} enregistrement(s) importé(s). ";
                    }
                    if ($summary['auto_generated_codes'] > 0) {
                        $message .= "{$summary['auto_generated_codes']} code(s) généré(s) automatiquement. ";
                    }
                    if ($summary['skipped'] > 0) {
                        $message .= "{$summary['skipped']} ligne(s) ignorée(s) (champs requis manquants). ";
                    }
                    if ($summary['errors'] > 0) {
                        $message .= "{$summary['errors']} erreur(s) rencontrée(s).";
                    }

                    return redirect()->route('records.index')->with('success', $message);
                case 'ead':
                    $service = new EADImportService();
                    $service->importRecordsFromString(file_get_contents($file->getPathname()), $dolly);
                    break;
                case 'seda':
                    $service = new SedaImportService();
                    $ext = strtolower($file->getClientOriginalExtension());
                    if ($ext === 'zip') {
                        $service->importRecordsFromZip($file->getPathname(), $dolly);
                    } else {
                        $service->importRecordsFromString(file_get_contents($file->getPathname()), $dolly);
                    }
                    break;
                default:
                    return redirect()->back()->with('error', 'Invalid import format');
            }
            return redirect()->route('records.index')->with('success', 'Records imported successfully and attached to new Dolly.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing records: ' . $e->getMessage());
        }
    }

    // Legacy EAD2002 generator removed in favor of App\Exports\EADExport (EAD3)

    private function exportSEDA($records, $slips)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
        <ArchiveTransfer xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="fr:gouv:culture:archivesdefrance:seda:v2.1 seda-2.1-main.xsd" xmlns="fr:gouv:culture:archivesdefrance:seda:v2.1">
        </ArchiveTransfer>');

        $xml->addChild('Comment', 'Archive Transfer');
        $xml->addChild('Date', date('Y-m-d'));

        $archive = $xml->addChild('Archive');

        foreach ($records as $record) {
            $archiveObject = $archive->addChild('ArchiveObject');
            $archiveObject->addChild('Name', $record->name);
            $archiveObject->addChild('Description', $record->content);

            $document = $archiveObject->addChild('Document');
            $document->addChild('Identification', $record->code);
            $document->addChild('Type', $record->level->name ?? 'item');

            foreach ($record->attachments as $attachment) {
                $attachmentNode = $document->addChild('Attachment');
                $attachmentNode->addChild('FileName', $attachment->name . '.pdf');
                $attachmentNode->addChild('Size', $attachment->size);
                $attachmentNode->addChild('Path', 'attachments/' . $attachment->name . '.pdf');
                $attachmentNode->addChild('Crypt', $attachment->crypt);
            }
        }

        // Format the XML with indentation
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        $formattedXml = $dom->saveXML();

        $zipFileName = 'records_seda_export_' . time() . '.zip';
        $zip = new ZipArchive();

        if ($zip->open(storage_path('app/public/' . $zipFileName), ZipArchive::CREATE) === TRUE) {
            $zip->addFromString('records.xml', $formattedXml);

            foreach ($records as $record) {
                foreach ($record->attachments as $attachment) {
                    $filePath = storage_path('app/' . $attachment->path);
                    if (file_exists($filePath)) {
                        $zip->addFile($filePath, 'attachments/' . $attachment->name . '.pdf');
                    }
                }
            }

            $zip->close();
        }

        return response()->download(storage_path('app/public/' . $zipFileName))->deleteFileAfterSend(true);
    }

    private function generateSEDA($records)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><ArchiveTransfer xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="fr:gouv:culture:archivesdefrance:seda:v2.1 seda-2.1-main.xsd" xmlns="fr:gouv:culture:archivesdefrance:seda:v2.1"></ArchiveTransfer>');

        $xml->addChild('Comment', 'Archive Transfer');
        $xml->addChild('Date', date('Y-m-d'));

        $archive = $xml->addChild('Archive');

        foreach ($records as $record) {
            $archiveObject = $archive->addChild('ArchiveObject');
            $archiveObject->addChild('Name', $record->name);
            $archiveObject->addChild('Description', $record->content);

            $document = $archiveObject->addChild('Document');
            $document->addChild('Identification', $record->code);
            $document->addChild('Type', $record->level->name ?? 'item');

            foreach ($record->attachments as $attachment) {
                $attachmentNode = $document->addChild('Attachment');
                $attachmentNode->addChild('FileName', $attachment->name . '.pdf');  // Added .pdf extension
                $attachmentNode->addChild('Size', $attachment->size);
                $attachmentNode->addChild('Path', 'attachments/' . $attachment->name . '.pdf');  // Added .pdf extension
                $attachmentNode->addChild('Crypt', $attachment->crypt);
            }
        }

        return $xml->asXML();
    }

    private function importEAD($file, $dolly)
    {
        $xml = simplexml_load_file($file);
        $xml->registerXPathNamespace('ead', 'urn:isbn:1-931666-22-9');

        $records = $xml->xpath('//ead:c');

        foreach ($records as $record) {
            $data = [
                'name' => (string)$record->did->unittitle,
                'date_start' => (string)$record->did->unitdate,
                'content' => (string)$record->scopecontent->p,
                // Map other fields as needed
            ];

            $newRecord = Record::create($data);
            $dolly->records()->attach($newRecord->id);
        }
    }

    private function importSEDA($file, $dolly)
    {
        $zip = new ZipArchive;
        $extractPath = storage_path('app/temp_import');

        if ($zip->open($file) === TRUE) {
            $zip->extractTo($extractPath);
            $zip->close();

            $xmlFile = $extractPath . '/records.xml';
            $xml = simplexml_load_file($xmlFile);
            $xml->registerXPathNamespace('seda', 'fr:gouv:culture:archivesdefrance:seda:v2.1');

            $records = $xml->xpath('//seda:ArchiveObject');

            foreach ($records as $record) {
                $data = [
                    'name' => (string)$record->Name,
                    'content' => (string)$record->Description,
                    'code' => (string)$record->Document->Identification,
                    // Map other fields as needed
                ];

                $newRecord = Record::create($data);
                $dolly->records()->attach($newRecord->id);
                // Import attachments
                $attachments = $record->xpath('Document/Attachment');
                foreach ($attachments as $attachment) {
                    $fileName = (string)$attachment->FileName;
                    $filePath = $extractPath . '/attachments/' . $fileName;

                    if (file_exists($filePath)) {
                        $hashMd5 = md5_file($filePath);
                        $hashSha512 = hash_file('sha512', $filePath);
                        $mimeType = mime_content_type($filePath);

                        $createdAttachment = Attachment::create([
                            'path' => 'attachments/' . $fileName,
                            'name' => $fileName,
                            'crypt' => $hashMd5,
                            'crypt_sha512' => $hashSha512,
                            'size' => (int)$attachment->Size,
                            'creator_id' => Auth::id(),
                            'type' => 'record',
                            'thumbnail_path' => '',
                            'mime_type' => $mimeType,
                        ]);

                        $newRecord->attachments()->attach($createdAttachment->id);

                        // Move file to the correct storage location
                        Storage::putFileAs('public/attachments', $filePath, $fileName);
                    }
                }
            }

            // Clean up temporary files
            Storage::deleteDirectory('temp_import');
        }
    }
    // EAD/SEDA import logic handled by services

    /**
     * Analyser un fichier pour extraire les en-têtes (remapping UI)
     */
    public function analyzeFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv',
            'format' => 'required|in:excel,csv',
        ]);

        try {
            $file = $request->file('file');
            $format = $request->input('format');
            $headers = [];
            $preview = [];

            if ($format === 'excel') {
                $headers = $this->extractExcelHeaders($file);
                $preview = $this->extractExcelPreview($file, 5);
            } else if ($format === 'csv') {
                $headers = $this->extractCsvHeaders($file);
                $preview = $this->extractCsvPreview($file, 5);
            }

            return response()->json([
                'success' => true,
                'headers' => $headers,
                'preview' => $preview,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'analyse du fichier: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'analyse du fichier: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Extraire les en-têtes d'un fichier Excel
     */
    private function extractExcelHeaders($file)
    {
        // Utiliser PhpSpreadsheet directement pour éviter l'appel avec un import null
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        if ($sheet->getHighestRow() < 1) {
            throw new \Exception('Le fichier est vide');
        }
        $highestColumn = $sheet->getHighestColumn();
        $headersRow = $sheet->rangeToArray('A1:' . $highestColumn . '1', null, true, true, true);
        $headersAssoc = $headersRow[1] ?? [];
        $headers = array_values(array_map(function ($value) {
            return is_string($value) ? trim($value) : (string) $value;
        }, array_values($headersAssoc)));
        return $headers;
    }

    /**
     * Extraire les en-têtes d'un fichier CSV
     */
    private function extractCsvHeaders($file)
    {
        $handle = fopen($file->getPathname(), 'r');
        if (!$handle) {
            throw new \Exception('Impossible de lire le fichier CSV');
        }
        $headers = fgetcsv($handle);
        fclose($handle);
        if (!$headers) {
            throw new \Exception('Impossible de lire les en-têtes du fichier CSV');
        }
        return $headers;
    }

    /**
     * Extraire un aperçu (quelques lignes) d'un Excel
     */
    private function extractExcelPreview($file, int $maxRows = 5): array
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        // Commencer après la 1ère ligne (en-têtes)
        $startRow = 2;
        $endRow = min($highestRow, 1 + $maxRows);
        if ($endRow < $startRow) {
            return [];
        }
        $range = 'A' . $startRow . ':' . $highestColumn . $endRow;
        $rows = $sheet->rangeToArray($range, null, true, true, false);
        // $rows est un array de lignes, chacune un array indexé 0..n par colonne
        return array_map(function ($row) {
            return array_map(function ($value) {
                if (is_null($value)) { return null; }
                return is_string($value) ? trim($value) : (string) $value;
            }, $row);
        }, $rows);
    }

    /**
     * Extraire un aperçu (quelques lignes) d'un CSV
     */
    private function extractCsvPreview($file, int $maxRows = 5): array
    {
        $handle = fopen($file->getPathname(), 'r');
        if (!$handle) {
            throw new \Exception('Impossible de lire le fichier CSV');
        }
        // Sauter la première ligne (en-têtes)
        fgetcsv($handle);
        $rows = [];
        $count = 0;
        while (($data = fgetcsv($handle)) !== false && $count < $maxRows) {
            $rows[] = array_map(function ($value) {
                return is_string($value) ? trim($value) : $value;
            }, $data);
            $count++;
        }
        fclose($handle);
        return $rows;
    }

    public function printRecords(Request $request)
    {
        Gate::authorize('records_export'); // ou créer une permission dédiée 'records_print'

        $recordIds = $request->input('records', []);
        if (!is_array($recordIds) || empty($recordIds)) {
            return redirect()->back()->with('error', __('Aucun document sélectionné.'));
        }

        // Eager loading des relations utilisées dans la vue d'impression
        $records = Record::with([
            'level','status','support','activity','containers','authors','thesaurusConcepts','attachments'
        ])->whereIn('id', $recordIds)->get();

        // Conserver l'ordre de sélection (tel qu'affiché côté interface)
        $idOrder = array_flip($recordIds);
        $records = $records->sortBy(fn($r) => $idOrder[$r->id])->values();

        $pdf = PDF::loadView('records.print', [
            'records' => $records,
        ]);

    // Mode flux (prévisualisation dans le navigateur) si demandé (query ou body)
    $mode = $request->query('mode', $request->input('mode'));
    if ($mode === 'stream') {
            return $pdf->stream('records_print.pdf');
        }
        return $pdf->download('records_print.pdf');
    }

    /**
     * Autocomplete pour les termes du thésaurus
     */
    public function autocompleteTerms(Request $request)
    {
        $query = $request->get('q', '');
        $limit = $request->get('limit', 10);

        if (strlen($query) < 3) {
            return response()->json([]);
        }

        try {
            // Recherche dans les labels (literal_form) des concepts du thésaurus
            $terms = ThesaurusConcept::with(['labels', 'scheme'])
                ->whereHas('labels', function($labelQuery) use ($query) {
                    $labelQuery->where('literal_form', 'LIKE', '%' . $query . '%')
                        ->whereIn('type', ['prefLabel', 'altLabel']);
                })
                ->where('status', 1)
                ->limit($limit)
                ->get()
                ->map(function ($concept) {
                    // Récupérer le label préféré
                    $prefLabel = $concept->labels()
                        ->where('type', 'prefLabel')
                        ->where('language', 'fr-fr')
                        ->first();

                    // Si pas de label préféré en français, prendre le premier disponible
                    if (!$prefLabel) {
                        $prefLabel = $concept->labels()
                            ->where('type', 'prefLabel')
                            ->first();
                    }

                    $labelText = $prefLabel ? $prefLabel->literal_form : $concept->uri;

                    return [
                        'id' => $concept->id,
                        'text' => $labelText,
                        'pref_label' => $labelText,
                        'scheme' => $concept->scheme ? $concept->scheme->title : 'Thésaurus',
                        'uri' => $concept->uri,
                        'language' => $prefLabel ? $prefLabel->language : 'fr-fr'
                    ];
                });

            return response()->json($terms);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la recherche de termes du thésaurus: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    /**
     * Récupérer les attachments d'un record pour l'affichage en modal
     */
    public function getAttachments(Record $record)
    {
        Gate::authorize('records_view');

        $record->load('attachments');

        return response()->json([
            'record' => [
                'id' => $record->id,
                'code' => $record->code,
                'name' => $record->name
            ],
            'attachments' => $record->attachments->map(function($attachment) {
                return [
                    'id' => $attachment->id,
                    'name' => $attachment->name,
                    'size' => $attachment->size,
                    'thumbnail_path' => $attachment->thumbnail_path,
                    'path' => $attachment->path
                ];
            })
        ]);
    }

    /**
     * Afficher le formulaire Drag & Drop
     */
    public function dragDropForm()
    {
        Gate::authorize('records_create');
        return view('records.drag-drop');
    }

    /**
     * Traiter les fichiers uploadés via Drag & Drop avec IA
     */
    public function processDragDrop(Request $request)
    {
        Gate::authorize('records_create');

        Log::info('Début processDragDrop', [
            'files_count' => count($request->file('files', [])),
            'user_id' => Auth::id()
        ]);

        // Validation des fichiers
        $request->validate([
            'files' => 'required|array|min:1|max:10',
            'files.*' => 'file|mimes:pdf,txt,docx,doc,rtf,odt,jpg,jpeg,png,gif|max:51200', // 50MB max
        ]);

        DB::beginTransaction();
        try {
            // 1. Créer un record temporaire
            Log::info('Création du record temporaire');
            $record = $this->createTemporaryRecord();
            Log::info('Record temporaire créé', ['record_id' => $record->id]);

            // 2. Traiter les fichiers uploadés
            Log::info('Traitement des fichiers uploadés');
            $attachments = $this->handleDragDropFiles($request->file('files'));
            Log::info('Fichiers traités', ['attachments_count' => $attachments->count()]);

            // 3. Associer les attachments au record
            Log::info('Association des attachments au record');
            $record->attachments()->attach($attachments->pluck('id'));

            // 4. Traiter avec l'IA
            Log::info('Début du traitement IA');
            $aiResponse = $this->processWithAI($record, $attachments);
            Log::info('Traitement IA terminé', ['response_preview' => substr(json_encode($aiResponse), 0, 200)]);

            // 5. Mettre à jour le record avec les suggestions IA
            Log::info('Application des suggestions IA');
            $this->applyAiSuggestions($record, $aiResponse);

            DB::commit();
            Log::info('Transaction commitée avec succès');

            return response()->json([
                'success' => true,
                'record_id' => $record->id,
                'ai_suggestions' => $aiResponse,
                'message' => 'Record créé avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erreur Drag & Drop: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un record temporaire
     */
    private function createTemporaryRecord(): Record
    {
        return Record::create([
            'code' => 'T' . substr(uniqid(), -8), // T + 8 chars = 9 chars total (within 10 char limit)
            'name' => 'Document en cours de traitement...',
            'date_format' => 'Y', // Format par défaut, sera mis à jour par l'IA
            'level_id' => RecordLevel::first()->id ?? 1,
            'status_id' => RecordStatus::first()->id ?? 1,
            'support_id' => RecordSupport::first()->id ?? 1,
            'activity_id' => Activity::first()->id ?? 1,
            'user_id' => Auth::id(),
            'content' => 'Record créé via Drag & Drop - En attente de traitement IA'
        ]);
    }

    /**
     * Traiter les fichiers uploadés
     */
    private function handleDragDropFiles(array $files): \Illuminate\Database\Eloquent\Collection
    {
        $attachmentIds = [];

        foreach ($files as $file) {
            // Stocker le fichier
            $path = $file->store('attachments/drag-drop');

            // Créer l'attachment
            $attachment = Attachment::create([
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'type' => $this->determineAttachmentType($file->getMimeType()),
                'creator_id' => Auth::id(),
                'crypt' => hash_file('md5', $file->getRealPath()),
                'crypt_sha512' => hash_file('sha512', $file->getRealPath()),
                'thumbnail_path' => '', // Pas de thumbnail pour les fichiers drag & drop
            ]);

            $attachmentIds[] = $attachment->id;
        }

        // Retourner une vraie Eloquent Collection
        return Attachment::whereIn('id', $attachmentIds)->get();
    }

    /**
     * Déterminer le type d'attachment basé sur le MIME type
     * Les valeurs possibles sont: 'mail','record','communication','transferting','bulletinboardpost','bulletinboard','bulletinboardevent'
     */
    private function determineAttachmentType(string $mimeType): string
    {
        // Pour les attachments créés via drag & drop, ils sont toujours liés aux records
        return 'record';
    }

    /**
     * Traiter avec l'IA
     */
    private function processWithAI(Record $record, \Illuminate\Database\Eloquent\Collection $attachments): array
    {
        try {
            // Extraire le texte de chaque attachment au lieu d'envoyer les fichiers binaires
            $contents = [];
            foreach ($attachments as $attachment) {
                $filePath = storage_path('app/' . $attachment->path);
                if (!file_exists($filePath)) {
                    Log::warning('Fichier introuvable pour extraction', [
                        'attachment_id' => $attachment->id ?? null,
                        'path' => $attachment->path,
                    ]);
                    continue;
                }

                $contents[] = $this->extractAttachmentContent($attachment, $filePath);
            }

            // Filtrer les extractions vides et tronquer pour éviter les prompts trop volumineux
            $contents = array_values(array_filter($contents, function ($c) {
                return !empty(trim($c['content'] ?? ''));
            }));

            if (empty($contents)) {
                throw new \Exception('Aucun contenu texte extrait des fichiers');
            }

            // Appliquer une limite globale de caractères pour éviter les payloads trop gros
            $maxTotalChars = (int) (app(\App\Services\SettingService::class)->get('ai_max_total_chars', 40000));
            $contents = $this->limitAggregateContents($contents, $maxTotalChars);

            // Construire le prompt pour l'IA avec le texte extrait (potentiellement tronqué)
            $prompt = $this->buildDragDropPrompt($contents);

            // Appeler l'IA SANS envoyer les fichiers
            $provider = app(\App\Services\SettingService::class)->get('ai_default_provider', 'ollama');
            $model = app(\App\Services\SettingService::class)->get('ai_default_model', 'gemma3:4b');

            // S'assurer que le provider est configuré
            app(\App\Services\AI\ProviderRegistry::class)->ensureConfigured($provider);

            Log::info('Envoi à l\'IA avec texte extrait (sans fichiers)', [
                'provider' => $provider,
                'model' => $model,
                'documents' => array_map(function ($c) { return [
                    'filename' => $c['filename'] ?? 'unknown',
                    'type' => $c['type'] ?? 'unknown',
                    'chars' => strlen($c['content'] ?? ''),
                ]; }, $contents),
            ]);

            // Utiliser AiBridge uniquement avec le prompt textuel
            $aiResponse = AiBridge::provider($provider)->chat([
                ['role' => 'user', 'content' => $prompt]
            ], [
                'model' => $model,
                'temperature' => 0.3,
                'max_tokens' => 1000,
                'timeout' => 120000 // 2 minutes pour traiter le contenu
            ]);

            // Parser la réponse
            $content = $this->extractText(is_array($aiResponse) ? $aiResponse : (array)$aiResponse);

            Log::info('Réponse IA reçue', [
                'content_length' => strlen($content),
                'response_preview' => substr($content, 0, 200)
            ]);

            return $this->parseAiDragDropResponse($content);

        } catch (\Exception $e) {
            Log::error('Erreur traitement IA Drag & Drop: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // Retourner une réponse par défaut
            return [
                'title' => 'Document importé le ' . now()->format('d/m/Y'),
                'content' => 'Document créé automatiquement via Drag & Drop.',
                'keywords' => [],
                'activity_suggestion' => null,
                'confidence' => 0.0
            ];
        }
    }

    /**
     * Extraire le contenu texte d'un attachment selon son type MIME
     * Retourne un tableau: [filename, type, content]
     */
    private function extractAttachmentContent($attachment, string $absolutePath): array
    {
        $filename = isset($attachment->name) ? (string) $attachment->name : basename($absolutePath);
        $mime = isset($attachment->mime_type) ? (string) $attachment->mime_type : mime_content_type($absolutePath);

        $type = 'unknown';
        $text = '';

        try {
            if (stripos($mime, 'pdf') !== false) {
                $type = 'pdf';
                $text = $this->extractTextFromPdf($absolutePath);
            } elseif (stripos($mime, 'text/plain') !== false || stripos($mime, 'csv') !== false) {
                $type = 'text';
                $text = @file_get_contents($absolutePath) ?: '';
            } elseif (stripos($mime, 'wordprocessingml') !== false || stripos($mime, 'officedocument') !== false || stripos($mime, 'vnd.oasis.opendocument.text') !== false || stripos($mime, 'rtf') !== false) {
                // DOCX / ODT / RTF
                $type = 'word';
                $text = $this->extractTextFromWordLike($absolutePath);
            } elseif (stripos($mime, 'msword') !== false) {
                // Ancien .doc - souvent non supporté en lecture; tentative via PhpWord, sinon vide
                $type = 'msword';
                $text = $this->extractTextFromWordLike($absolutePath);
            } elseif (stripos($mime, 'image/') === 0) {
                $type = 'image';
                $text = $this->extractTextFromImage($absolutePath);
            } else {
                // Fallback basique: tenter lecture en texte
                $type = 'binary';
                $raw = @file_get_contents($absolutePath) ?: '';
                // Ne pas envoyer du binaire; tronquer et filtrer
                $text = $this->sanitizeToText($raw);
            }
        } catch (\Throwable $e) {
            Log::warning('Erreur extraction de texte', [
                'filename' => $filename,
                'mime' => $mime,
                'error' => $e->getMessage(),
            ]);
            $text = '';
        }

        // Nettoyage et troncature pour limiter la taille envoyée à l'IA
        $text = $this->normalizeWhitespace($text);
        $text = $this->truncateMiddle($text, 20000); // max 20k chars par document

        return [
            'filename' => $filename,
            'type' => $type,
            'content' => $text,
        ];
    }

    /**
     * Extraction texte depuis PDF (texte natif), fallback OCR si nécessaire si Imagick + Tesseract dispos
     */
    private function extractTextFromPdf(string $path): string
    {
        $text = '';
        try {
            if (class_exists(\Smalot\PdfParser\Parser::class)) {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($path);
                $text = $pdf->getText();
            }
        } catch (\Throwable $e) {
            Log::info('PDFParser a échoué, tentative OCR si possible', ['error' => $e->getMessage()]);
        }

        // Si texte quasi vide, tenter OCR basique page par page si Imagick et Tesseract disponibles
    if (mb_strlen(trim($text)) < 30 && extension_loaded('imagick') && class_exists('Imagick') && class_exists(\thiagoalessio\TesseractOCR\TesseractOCR::class)) {
            try {
                $imClass = '\\Imagick';
                $imagick = new $imClass();
                // Résolution plus élevée pour de meilleurs résultats OCR
                $imagick->setResolution(300, 300);
                $imagick->readImage($path);
                $ocrText = '';
                foreach ($imagick as $i => $page) {
                    // Prétraitement: niveaux de gris, contraste, sharpen
                    $imagickClass = '\\Imagick';
                    $page->setImageColorspace($imagickClass::COLORSPACE_GRAY);
                    $page->enhanceImage();
                    $page->contrastStretchImage(0.1, 0.9);
                    $page->unsharpMaskImage(0.5, 0.5, 0.7, 0.0);
                    $page->setImageFormat('png');
                    $tmp = tempnam(sys_get_temp_dir(), 'pdfpg_') . '.png';
                    $page->writeImage($tmp);
                    try {
                        $ocr = new \thiagoalessio\TesseractOCR\TesseractOCR($tmp);
                        // Essayer FR puis EN
                        $ocr->lang('fra', 'eng');
                        // Améliorer la qualité d'extraction: OEM LSTM, PSM 3 (auto)
                        $ocr->oem(1)->psm(3);
                        $ocrText .= "\n" . $ocr->run();
                    } catch (\Throwable $e) {
                        Log::debug('OCR tesseract erreur page', ['page' => $i, 'error' => $e->getMessage()]);
                    } finally {
                        @unlink($tmp);
                    }
                }
                $text = trim($ocrText) ?: $text;
            } catch (\Throwable $e) {
                Log::debug('OCR PDF fallback échoué', ['error' => $e->getMessage()]);
            }
        }

        return $text;
    }

    /**
     * Extraction texte depuis images via Tesseract (si dispo)
     */
    private function extractTextFromImage(string $path): string
    {
        if (!class_exists(\thiagoalessio\TesseractOCR\TesseractOCR::class)) {
            return '';
        }
        try {
            $ocr = new \thiagoalessio\TesseractOCR\TesseractOCR($path);
            $ocr->lang('fra', 'eng');
            return $ocr->run();
        } catch (\Throwable $e) {
            Log::debug('OCR image échoué', ['error' => $e->getMessage()]);
            return '';
        }
    }

    /**
     * Extraction texte depuis DOCX/ODT/RTF (via PhpWord si disponible)
     */
    private function extractTextFromWordLike(string $path): string
    {
        if (!class_exists(\PhpOffice\PhpWord\IOFactory::class)) {
            return '';
        }
        try {
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($path);
            $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
            $stream = fopen('php://temp', 'r+');
            $writer->save($stream);
            rewind($stream);
            $html = stream_get_contents($stream);
            fclose($stream);
            // Nettoyer le HTML en texte brut
            $text = strip_tags($html);
            return $text;
        } catch (\Throwable $e) {
            Log::debug('Extraction WordLike échouée', ['error' => $e->getMessage()]);
            return '';
        }
    }

    /**
     * Remplacer le binaire par un texte sûr (enlevant les null bytes, non-UTF8)
     */
    private function sanitizeToText(string $raw): string
    {
        // Forcer en UTF-8
        $utf8 = @mb_convert_encoding($raw, 'UTF-8', 'auto');
        $utf8 = is_string($utf8) ? $utf8 : $raw;
        // Retirer les caractères de contrôle
        $utf8 = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', ' ', $utf8);
        return $utf8 ?? '';
    }

    /**
     * Normaliser les espaces/blancs
     */
    private function normalizeWhitespace(string $text): string
    {
        // Unifier les fins de ligne et compresser les espaces répétés
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/\x{FEFF}/u', '', $text); // BOM
        // Limiter les suites d'espaces
        $text = preg_replace('/[\t ]{2,}/', ' ', $text);
        // Limiter les lignes vides consécutives
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        return trim($text);
    }

    /**
     * Tronquer une longue chaîne en conservant le début et la fin
     */
    private function truncateMiddle(string $text, int $maxLen): string
    {
        $len = mb_strlen($text);
        if ($len <= $maxLen) return $text;
        $keep = (int) floor($maxLen / 2);
        return mb_substr($text, 0, $keep) . "\n...\n" . mb_substr($text, -$keep);
    }

    /**
     * Limiter le volume total de texte agrégé envoyé à l'IA.
     * - maxTotalChars: budget global (ex. 40k)
     * - On réserve un peu pour l'en-tête par document, puis on répartit le budget de contenu.
     */
    private function limitAggregateContents(array $contents, int $maxTotalChars): array
    {
        // Estimations: en-tête par doc ~120 chars
        $headerPerDoc = 120;
        $docCount = max(1, count($contents));
        $reserved = $headerPerDoc * $docCount;
        $budget = max(1000, $maxTotalChars - $reserved); // garder un minimum

        // Calculer le total actuel
        $total = 0;
        foreach ($contents as $c) {
            $total += mb_strlen($c['content'] ?? '');
        }
        if ($total <= $budget) return $contents; // rien à faire

        // Répartir le budget proportionnellement
        $result = [];
        foreach ($contents as $c) {
            $text = $c['content'] ?? '';
            $len = max(1, mb_strlen($text));
            $share = (int) floor($budget * ($len / $total));
            $share = max(500, $share); // au moins un peu de contexte
            $c['content'] = $this->truncateMiddle($text, $share);
            $result[] = $c;
        }
        // Double check: ne pas dépasser le budget à cause des arrondis et du min.
        $used = 0; foreach ($result as $r) { $used += mb_strlen($r['content'] ?? ''); }
        while ($used > $budget) {
            $delta = $used - $budget;
            // Trouver l'index du doc le plus long
            $maxIdx = 0; $maxLen = -1;
            foreach ($result as $idx => $r) {
                $l = mb_strlen($r['content'] ?? '');
                if ($l > $maxLen) { $maxLen = $l; $maxIdx = $idx; }
            }
            if ($maxLen <= 600) { break; } // éviter de trop réduire
            $reduce = min($delta, max(100, (int) floor($maxLen * 0.1))); // réduire par pas raisonnables
            $newLen = max(500, $maxLen - $reduce);
            $result[$maxIdx]['content'] = $this->truncateMiddle($result[$maxIdx]['content'], $newLen);
            // recalculer 'used'
            $used = 0; foreach ($result as $r) { $used += mb_strlen($r['content'] ?? ''); }
        }
        return $result;
    }

    /**
     * Construire le prompt pour l'IA avec fichiers
     */
    private function buildDragDropPromptWithFiles(): string
    {
        return "Tu es un assistant spécialisé en archivage. Je vais te transmettre des fichiers uploadés via un système de drag & drop. Analyse le contenu de tous les fichiers et propose une description structurée pour créer un record archivistique.

INSTRUCTIONS:
1. Analyse le contenu de TOUS les fichiers fournis
2. Propose un titre pertinent et concis (max 100 caractères) qui résume l'ensemble
3. Rédige une description/résumé du contenu de tous les fichiers (max 500 mots)
4. Suggère 3-5 mots-clés pertinents basés sur le contenu
5. Identifie les dates dans le contenu (date_start, date_end si période, ou date_exact si date précise)
6. Évalue le niveau de confiance de tes suggestions (0-1)

RÉPONSE ATTENDUE (JSON strict):
{
  \"title\": \"Titre proposé basé sur tous les fichiers\",
  \"content\": \"Description détaillée du contenu analysé\",
  \"keywords\": [\"mot1\", \"mot2\", \"mot3\"],
  \"date_start\": \"YYYY-MM-DD ou YYYY ou YYYY-MM\",
  \"date_end\": \"YYYY-MM-DD ou YYYY ou YYYY-MM\",
  \"date_exact\": \"YYYY-MM-DD\",
  \"confidence\": 0.85,
  \"summary\": \"Résumé en une phrase de l'ensemble des documents\"
}

NOTES SUR LES DATES:
- Si tu trouves une date précise, utilise \"date_exact\"
- Si tu trouves une période, utilise \"date_start\" et \"date_end\"
- Si tu trouves seulement une année, utilise le format \"YYYY\"
- Si tu trouves année-mois, utilise le format \"YYYY-MM\"
- Laisse null les champs de date si aucune date n'est identifiable

Réponds UNIQUEMENT en JSON valide, sans texte additionnel.";
    }

    /**
     * Construire le prompt pour l'IA (version legacy avec extraction de texte)
     */
    private function buildDragDropPrompt(array $contents): string
    {
        $contentText = '';
        foreach ($contents as $content) {
            $contentText .= "=== Fichier: {$content['filename']} ({$content['type']}) ===\n";
            $contentText .= $content['content'] . "\n\n";
        }

        return "Tu es un assistant spécialisé en archivage. Analyse le contenu suivant et propose une description structurée pour créer un record archivistique.

CONTENU À ANALYSER:
{$contentText}

INSTRUCTIONS:
1. Propose un titre pertinent et concis (max 100 caractères)
2. Rédige une description/résumé du contenu (max 500 mots)
3. Suggère 3-5 mots-clés pertinents
4. Identifie les dates dans le contenu (date_start, date_end si période, ou date_exact si date précise)
5. Évalue le niveau de confiance de tes suggestions (0-1)

RÉPONSE ATTENDUE (JSON strict):
{
  \"title\": \"Titre proposé\",
  \"content\": \"Description détaillée\",
  \"keywords\": [\"mot1\", \"mot2\", \"mot3\"],
  \"date_start\": \"YYYY-MM-DD ou YYYY ou YYYY-MM\",
  \"date_end\": \"YYYY-MM-DD ou YYYY ou YYYY-MM\",
  \"date_exact\": \"YYYY-MM-DD\",
  \"confidence\": 0.85,
  \"summary\": \"Résumé en une phrase\"
}

NOTES SUR LES DATES:
- Si tu trouves une date précise, utilise \"date_exact\"
- Si tu trouves une période, utilise \"date_start\" et \"date_end\"
- Si tu trouves seulement une année, utilise le format \"YYYY\"
- Si tu trouves année-mois, utilise le format \"YYYY-MM\"
- Laisse null les champs de date si aucune date n'est identifiable

Réponds UNIQUEMENT en JSON valide, sans texte additionnel.";
    }

    /**
     * Parser la réponse de l'IA
     */
    private function parseAiDragDropResponse(string $response): array
    {
        try {
            // Nettoyer la réponse
            $cleaned = trim($response);
            $cleaned = preg_replace('/^```json\s*/', '', $cleaned);
            $cleaned = preg_replace('/\s*```$/', '', $cleaned);

            $data = json_decode($cleaned, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON invalide: ' . json_last_error_msg());
            }

            return [
                'title' => $data['title'] ?? 'Document sans titre',
                'content' => $data['content'] ?? '',
                'keywords' => $data['keywords'] ?? [],
                'confidence' => $data['confidence'] ?? 0.5,
                'summary' => $data['summary'] ?? '',
                'date_start' => $data['date_start'] ?? null,
                'date_end' => $data['date_end'] ?? null,
                'date_exact' => $data['date_exact'] ?? null
            ];

        } catch (\Exception $e) {
            Log::warning('Erreur parsing réponse IA: ' . $e->getMessage(), [
                'response' => $response
            ]);

            return [
                'title' => 'Document importé',
                'content' => 'Contenu extrait automatiquement.',
                'keywords' => [],
                'confidence' => 0.0,
                'summary' => '',
                'date_start' => null,
                'date_end' => null,
                'date_exact' => null
            ];
        }
    }    /**
     * Appliquer les suggestions de l'IA au record
     */
    private function applyAiSuggestions(Record $record, array $aiResponse): void
    {
        // Préparer les données de mise à jour
        $updateData = [
            'name' => $aiResponse['title'] ?? $record->name,
            'content' => $aiResponse['content'] ?? $record->content,
            'code' => 'DD' . now()->format('md') . sprintf('%03d', $record->id), // DD + MMDD + ID padded = max 10 chars
        ];

        // Traitement des dates suggérées par l'IA
        if (!empty($aiResponse['date_exact'])) {
            // Date exacte fournie
            $updateData['date_exact'] = $aiResponse['date_exact'];
            $updateData['date_format'] = 'D'; // Format complet AAAA/MM/DD
        } elseif (!empty($aiResponse['date_start']) || !empty($aiResponse['date_end'])) {
            // Période fournie
            $dateStart = $aiResponse['date_start'] ?? null;
            $dateEnd = $aiResponse['date_end'] ?? null;

            if ($dateStart) $updateData['date_start'] = $dateStart;
            if ($dateEnd) $updateData['date_end'] = $dateEnd;

            // Utiliser la méthode existante pour déterminer le format
            $updateData['date_format'] = $this->getDateFormat($dateStart, $dateEnd);
        }
        // Si aucune date n'est fournie, garder le format 'Y' par défaut

        // Mettre à jour le record
        $record->update($updateData);

        // Traiter les mots-clés si fournis
        if (!empty($aiResponse['keywords'])) {
            $keywords = \App\Models\Keyword::processKeywordsString(implode(', ', $aiResponse['keywords']));
            $record->keywords()->attach($keywords->pluck('id'));
        }
    }

    /**
     * Extraire le texte de la réponse IA (réutilise la méthode existante)
     */
    private function extractText($response): string
    {
        if (is_string($response)) {
            return $response;
        }

        if (is_array($response)) {
            return $response['content'] ?? $response['message'] ?? $response['text'] ?? json_encode($response);
        }

        return (string) $response;
    }
}
