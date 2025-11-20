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
use App\Models\RecordPhysical;
use App\Models\RecordDigitalFolder;
use App\Models\RecordDigitalDocument;
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

        // Si aucun critère de recherche n'est fourni, retourner une collection vide
        if (empty($query) && empty($keywordFilter)) {
            $records = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                10,
                $request->input('page', 1),
                ['path' => $request->url(), 'query' => $request->query()]
            );

            $slipStatuses = SlipStatus::all();
            $statuses = RecordStatus::all();
            $terms = [];
            $users = User::select('id', 'name')->get();
            $organisations = Organisation::select('id', 'name')->get();

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

        // Initialiser les queries pour les 3 types de records
        $physicalQuery = RecordPhysical::with([
            'level', 'status', 'support', 'activity', 'containers', 'authors', 'thesaurusConcepts', 'attachments', 'keywords'
        ]);

        $foldersQuery = RecordDigitalFolder::with([
            'level', 'status', 'activity', 'authors', 'keywords', 'thesaurusConcepts', 'attachments'
        ]);

        $documentsQuery = RecordDigitalDocument::with([
            'level', 'status', 'activity', 'versions', 'keywords', 'thesaurusConcepts', 'attachments'
        ]);

        // Recherche par query (name et code) pour tous les types
        if (!empty($query)) {
            $physicalQuery->where(function($q) use ($query) {
                $q->where('name', 'LIKE', '%' . $query . '%')
                    ->orWhere('code', 'LIKE', '%' . $query . '%');
            });

            $foldersQuery->where(function($q) use ($query) {
                $q->where('name', 'LIKE', '%' . $query . '%')
                    ->orWhere('code', 'LIKE', '%' . $query . '%');
            });

            $documentsQuery->where(function($q) use ($query) {
                $q->where('name', 'LIKE', '%' . $query . '%')
                    ->orWhere('code', 'LIKE', '%' . $query . '%');
            });
        }

        // Filtrage par mot-clé si fourni (appliqué aux 3 types)
        if (!empty($keywordFilter)) {
            $physicalQuery->whereHas('keywords', function ($q) use ($keywordFilter) {
                $q->where('name', 'LIKE', '%' . $keywordFilter . '%');
            });
            $foldersQuery->whereHas('keywords', function ($q) use ($keywordFilter) {
                $q->where('name', 'LIKE', '%' . $keywordFilter . '%');
            });
            $documentsQuery->whereHas('keywords', function ($q) use ($keywordFilter) {
                $q->where('name', 'LIKE', '%' . $keywordFilter . '%');
            });
        }

        // Récupérer les résultats
        $physicalRecords = $physicalQuery->get();
        $folders = $foldersQuery->get();
        $documents = $documentsQuery->get();

        // Combiner tous les records avec leurs types
        $allRecords = collect();

        foreach ($physicalRecords as $record) {
            $record->record_type = 'physical';
            $record->type_label = $this->getRecordTypeLabel('physical');
            $allRecords->push($record);
        }

        foreach ($folders as $folder) {
            $folder->record_type = 'folder';
            $folder->type_label = $this->getRecordTypeLabel('folder');
            $allRecords->push($folder);
        }

        foreach ($documents as $document) {
            $document->record_type = 'document';
            $document->type_label = $this->getRecordTypeLabel('document');
            $allRecords->push($document);
        }

        // Pagination manuelle
        $perPage = 10;
        $page = $request->input('page', 1);
        $records = new \Illuminate\Pagination\LengthAwarePaginator(
            $allRecords->forPage($page, $perPage),
            $allRecords->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Charger les données nécessaires pour la vue index
        $slipStatuses = SlipStatus::all();
        $statuses = RecordStatus::all();
        $terms = [];
        $users = User::select('id', 'name')->get();
        $organisations = Organisation::select('id', 'name')->get();

        // Contexte de navigation pour une expérience fluide
        $listIds = $allRecords->map(function($record) {
            return $record->record_type . '_' . $record->id;
        })->toArray();

        session([
            'records.back_url' => $request->fullUrl(),
            'records.list_ids' => $listIds,
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

        // Initialiser les queries pour les 3 types de records
        $physicalQuery = RecordPhysical::with([
            'level', 'status', 'support', 'activity', 'containers', 'authors', 'thesaurusConcepts', 'keywords'
        ]);

        $foldersQuery = RecordDigitalFolder::with([
            'type', 'creator', 'organisation'
        ]);

        $documentsQuery = RecordDigitalDocument::with([
            'type', 'creator', 'organisation', 'folder'
        ]);

        // Filtrage par mot-clé si fourni (appliqué aux records physiques uniquement)
        $keywordFilter = $request->input('keyword_filter');
        if ($request->filled('keyword_filter')) {
            $physicalQuery->whereHas('keywords', function ($q) use ($keywordFilter) {
                $q->where('name', 'LIKE', '%' . $keywordFilter . '%');
            });
            // Note: Keywords filtering not yet implemented for digital folders and documents
        }

        // Récupérer les résultats
        $physicalRecords = $physicalQuery->get();
        $folders = $foldersQuery->get();
        $documents = $documentsQuery->get();

        // Combiner tous les records avec leurs types
        $allRecords = collect();

        foreach ($physicalRecords as $record) {
            $record->record_type = 'physical';
            $record->type_label = $this->getRecordTypeLabel('physical');
            $allRecords->push($record);
        }

        foreach ($folders as $folder) {
            $folder->record_type = 'folder';
            $folder->type_label = $this->getRecordTypeLabel('folder');
            $allRecords->push($folder);
        }

        foreach ($documents as $document) {
            $document->record_type = 'document';
            $document->type_label = $this->getRecordTypeLabel('document');
            $allRecords->push($document);
        }

        // Pagination manuelle
        $perPage = 10;
        $page = $request->input('page', 1);
        $records = new \Illuminate\Pagination\LengthAwarePaginator(
            $allRecords->forPage($page, $perPage),
            $allRecords->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $slipStatuses = SlipStatus::all();
        $statuses = RecordStatus::all();
        $terms = [];
        $users = User::select('id', 'name')->get();
        $organisations = Organisation::select('id', 'name')->get();

        // Contexte de navigation pour une expérience fluide
        // On stocke les IDs avec préfixe de type pour distinguer les records
        $listIds = $allRecords->map(function($record) {
            return $record->record_type . '_' . $record->id;
        })->toArray();

        session([
            'records.back_url' => $request->fullUrl(),
            'records.list_ids' => $listIds,
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

    /**
     * Display only physical records (harmonized route)
     */
    public function indexPhysical(Request $request)
    {
        Gate::authorize('records_view');

        // Query uniquement pour les records physiques
        $query = RecordPhysical::with([
            'level', 'status', 'support', 'activity', 'containers', 'authors', 'thesaurusConcepts', 'keywords'
        ]);

        // Filtrage par mot-clé si fourni
        $keywordFilter = $request->input('keyword_filter');
        if ($request->filled('keyword_filter')) {
            $query->whereHas('keywords', function ($q) use ($keywordFilter) {
                $q->where('name', 'LIKE', '%' . $keywordFilter . '%');
            });
        }

        // Pagination
        $records = $query->paginate(10);

        // Ajouter le type aux records
        foreach ($records as $record) {
            $record->record_type = 'physical';
            $record->type_label = $this->getRecordTypeLabel('physical');
        }

        $slipStatuses = SlipStatus::all();
        $statuses = RecordStatus::all();
        $terms = [];
        $users = User::select('id', 'name')->get();
        $organisations = Organisation::select('id', 'name')->get();

        // Contexte de navigation
        $listIds = $records->pluck('id')->map(function($id) {
            return 'physical_' . $id;
        })->toArray();

        session([
            'records.back_url' => $request->fullUrl(),
            'records.list_ids' => $listIds,
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
        $parents = RecordPhysical::all();
        $containers = Container::all();
        $users = User::all();
        $levels = RecordLevel::all();
        $records = RecordPhysical::all();
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
        $parents = RecordPhysical::all();
        $containers = Container::all();
        $users = User::all();
        $levels = RecordLevel::all();
        $records = RecordPhysical::all();
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
            $record = RecordPhysical::create($recordData);
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
            'record_exists' => RecordPhysical::where('id', $record->id)->exists()
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

    public function show(RecordPhysical $record, Request $request)
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

    public function showFull(RecordPhysical $record, Request $request)
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

    public function edit(RecordPhysical $record, Request $request)
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
        $parents = RecordPhysical::all();
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

    public function update(Request $request, RecordPhysical $record)
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

    public function destroy(RecordPhysical $record)
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

        $recordIdsRaw = explode(',', $request->query('records'));
        $format = $request->query('format', 'excel');

        // Séparer les IDs par type (format: "type_id" ou juste "id" pour legacy)
        $physicalIds = [];
        $folderIds = [];
        $documentIds = [];

        foreach ($recordIdsRaw as $idStr) {
            $idStr = trim($idStr);
            if (str_contains($idStr, '_')) {
                [$type, $id] = explode('_', $idStr, 2);
                if ($type === 'physical') {
                    $physicalIds[] = $id;
                } elseif ($type === 'folder') {
                    $folderIds[] = $id;
                } elseif ($type === 'document') {
                    $documentIds[] = $id;
                }
            } else {
                // Legacy: IDs sans préfixe sont considérés comme physical
                $physicalIds[] = $idStr;
            }
        }

        // Charger les records de chaque type avec leurs relations
        $physicalRecords = collect();
        $folders = collect();
        $documents = collect();

        if (!empty($physicalIds)) {
            $physicalRecords = RecordPhysical::with([
                'level','status','support','activity','organisation',
                'containers','recordContainers.container','authors','thesaurusConcepts','attachments',
                'children'
            ])->whereIn('id', $physicalIds)->get()->map(function($r) {
                $r->record_type = 'physical';
                $r->type_label = 'Dossier Physique';
                return $r;
            });
        }

        if (!empty($folderIds)) {
            $folders = RecordDigitalFolder::with([
                'level','status','activity','organisation','authors','keywords','thesaurusConcepts','attachments','parent'
            ])->whereIn('id', $folderIds)->get()->map(function($f) {
                $f->record_type = 'folder';
                $f->type_label = 'Dossier Numérique';
                return $f;
            });
        }

        if (!empty($documentIds)) {
            $documents = RecordDigitalDocument::with([
                'level','status','activity','organisation','versions','keywords','thesaurusConcepts','attachments','folder'
            ])->whereIn('id', $documentIds)->get()->map(function($d) {
                $d->record_type = 'document';
                $d->type_label = 'Document Numérique';
                return $d;
            });
        }

        // Combiner tous les records
        $allRecords = $physicalRecords->concat($folders)->concat($documents);

        $slips = "";
        try {
            switch ($format) {
                case 'excel':
                    // Excel peut exporter tous les types ensemble
                    return Excel::download(new RecordsExport($allRecords), 'records_export.xlsx');

                case 'ead':
                case 'ead2002':
                case 'dublincore':
                case 'seda':
                    // Ces formats sont spécifiques aux archives physiques
                    if ($physicalRecords->isEmpty()) {
                        return response()->json([
                            'error' => 'Les formats EAD/SEDA/DublinCore ne sont disponibles que pour les dossiers physiques.'
                        ], 400);
                    }

                    if ($format === 'ead') {
                        $ead = new \App\Exports\EADExport();
                        $xml = $ead->exportRecords($physicalRecords);
                        return response($xml)
                            ->header('Content-Type', 'application/xml')
                            ->header('Content-Disposition', 'attachment; filename="records_export.xml"');
                    } elseif ($format === 'ead2002') {
                        $ead2002 = new \App\Services\EAD2002ExportService();
                        $xml = $ead2002->exportRecords($physicalRecords);
                        return response($xml)
                            ->header('Content-Type', 'application/xml')
                            ->header('Content-Disposition', 'attachment; filename="records_export_ead2002.xml"');
                    } elseif ($format === 'dublincore') {
                        $dc = new \App\Services\DublinCoreExportService();
                        $xml = $dc->exportRecords($physicalRecords);
                        return response($xml)
                            ->header('Content-Type', 'application/xml')
                            ->header('Content-Disposition', 'attachment; filename="records_export_dublincore.xml"');
                    } else { // seda
                        return $this->exportSEDA($physicalRecords, $slips);
                    }

                case 'pdf':
                    // PDF peut exporter tous les types ensemble
                    // Conserver l'ordre de sélection original
                    $recordsForPdf = $allRecords;

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
            $records = RecordPhysical::with([
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

            $newRecord = RecordPhysical::create($data);
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

                $newRecord = RecordPhysical::create($data);
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

        // Séparer les IDs par type (format: "type_id" ou juste "id" pour legacy)
        $physicalIds = [];
        $folderIds = [];
        $documentIds = [];

        foreach ($recordIds as $idStr) {
            $idStr = trim($idStr);
            if (str_contains($idStr, '_')) {
                [$type, $id] = explode('_', $idStr, 2);
                if ($type === 'physical') {
                    $physicalIds[] = $id;
                } elseif ($type === 'folder') {
                    $folderIds[] = $id;
                } elseif ($type === 'document') {
                    $documentIds[] = $id;
                }
            } else {
                // Legacy: IDs sans préfixe sont considérés comme physical
                $physicalIds[] = $idStr;
            }
        }

        // Charger les records de chaque type avec leurs relations
        $physicalRecords = collect();
        $folders = collect();
        $documents = collect();

        if (!empty($physicalIds)) {
            $physicalRecords = RecordPhysical::with([
                'level','status','support','activity','containers','authors','thesaurusConcepts','attachments'
            ])->whereIn('id', $physicalIds)->get()->map(function($r) {
                $r->record_type = 'physical';
                $r->type_label = 'Dossier Physique';
                return $r;
            });
        }

        if (!empty($folderIds)) {
            $folders = RecordDigitalFolder::with([
                'level','status','activity','authors','keywords','thesaurusConcepts','attachments'
            ])->whereIn('id', $folderIds)->get()->map(function($f) {
                $f->record_type = 'folder';
                $f->type_label = 'Dossier Numérique';
                return $f;
            });
        }

        if (!empty($documentIds)) {
            $documents = RecordDigitalDocument::with([
                'level','status','activity','versions','keywords','thesaurusConcepts','attachments'
            ])->whereIn('id', $documentIds)->get()->map(function($d) {
                $d->record_type = 'document';
                $d->type_label = 'Document Numérique';
                return $d;
            });
        }

        // Combiner tous les records
        $records = $physicalRecords->concat($folders)->concat($documents);

        // Conserver l'ordre de sélection (tel qu'affiché côté interface)
        // Note: Tri simplifié car les IDs peuvent être de modèles différents
        // On pourrait améliorer en créant un mapping position => record

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
    public function getAttachments(RecordPhysical $record)
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
     * Détecte le type de record et retourne le modèle approprié
     *
     * @param string $type Type du record ('physical', 'folder', 'document')
     * @return string Nom de classe du modèle
     */
    private function getRecordModel(string $type): string
    {
        return match($type) {
            'physical' => RecordPhysical::class,
            'folder' => RecordDigitalFolder::class,
            'document' => RecordDigitalDocument::class,
            default => RecordPhysical::class,
        };
    }

    /**
     * Trouve un record par son ID et son type
     *
     * @param int $id ID du record
     * @param string $type Type du record ('physical', 'folder', 'document')
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    private function findRecord(int $id, string $type)
    {
        $modelClass = $this->getRecordModel($type);
        return $modelClass::find($id);
    }

    /**
     * Récupère le label pour le type de record
     *
     * @param string $type Type du record ('physical', 'folder', 'document')
     * @return string Label traduit
     */
    private function getRecordTypeLabel(string $type): string
    {
        return match($type) {
            'physical' => 'Dossier Physique',
            'folder' => 'Dossier Numérique',
            'document' => 'Document Numérique',
            default => 'Dossier Physique',
        };
    }

}
