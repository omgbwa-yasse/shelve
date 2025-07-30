<?php

namespace App\Http\Controllers;

use App\Exports\RecordsExport;
use App\Imports\RecordsImport;
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
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (!empty($query)) {
            // Recherche dans l'intitulé (name) et le code
            $records = Record::where(function($q) use ($query) {
                $q->where('name', 'LIKE', '%' . $query . '%')
                  ->orWhere('code', 'LIKE', '%' . $query . '%');
            })
            ->with(['level', 'status', 'support', 'activity', 'containers', 'authors', 'thesaurusConcepts'])
            ->paginate(10);
        } else {
            // Si pas de query, retourner une collection vide paginée
            $records = Record::where('id', 0)->paginate(10);
        }

        // Charger les données nécessaires pour la vue index
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

    public function index()
    {
        $this->authorize('viewAny', Record::class);

        if (Gate::allows('viewAny', Record::class)) {
            // L'utilisateur a la permission de voir tous les records
            $records = Record::with([
                'level', 'status', 'support', 'activity', 'containers', 'authors', 'thesaurusConcepts'
            ])->paginate(10);
        } else {
            // L'utilisateur ne peut voir que les records associés aux activités de son organisation actuelle
            $currentOrganisationId = auth::user()->current_organisation_id;

            $records = Record::with([
                'level', 'status', 'support', 'activity', 'containers', 'authors', 'thesaurusConcepts'
            ])
                ->whereHas('activity', function($query) use ($currentOrganisationId) {
                    $query->whereHas('organisations', function($q) use ($currentOrganisationId) {
                        $q->where('organisations.id', $currentOrganisationId);
                    });
                })
                ->paginate(10);
        }

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
            'organisations'
        ));
    }

    public function create()
    {
        $this->authorize('create', Record::class);

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
        $this->authorize('create', Record::class);

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
        $this->authorize('create', Record::class);

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
            'container_id' => 'nullable|integer|exists:containers,id',
            'accession_id' => 'nullable|integer|exists:accessions,id',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        // Supprimer author_ids et term_ids des données validées car ils ne sont pas des champs de la table
        $recordData = $validatedData;

        $record = Record::create($recordData);

        // Traitement des auteurs (obligatoire)
        $author_ids = $request->input('author_ids', []);
        if (is_string($author_ids)) {
            $author_ids = explode(',', $author_ids);
        }
        if (isset($author_ids[0]) && is_string($author_ids[0])) {
            $author_ids = explode(',', $author_ids[0]);
        }

        $author_ids = array_filter(array_map('intval', $author_ids));

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
        $term_ids = $request->input('term_ids', []);
        if (is_string($term_ids)) {
            $term_ids = explode(',', $term_ids);
        }
        if (isset($term_ids[0]) && is_string($term_ids[0])) {
            $term_ids = explode(',', $term_ids[0]);
        }

        $term_ids = array_filter(array_map('intval', $term_ids));

        foreach ($term_ids as $term_id) {
            if ($term_id > 0) {
                $record->thesaurusConcepts()->attach($term_id, [
                    'weight' => 1.0,
                    'context' => 'manuel',
                    'extraction_note' => null
                ]);
            }
        }

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

    public function show(Record $record)
    {
        $this->authorize('view', $record);

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

        return view('records.show', compact('record'));
    }

    public function showFull(Record $record)
    {
        $this->authorize('view', $record);

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

        return view('records.showFull', compact('record'));
    }

    public function edit(Record $record, Request $request)
    {
        $this->authorize('update', $record);

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
        $this->authorize('update', $record);

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
            'container_id' => 'nullable|integer|exists:containers,id',
            'accession_id' => 'nullable|integer|exists:accessions,id',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        // Supprimer author_ids et term_ids des données validées car ils ne sont pas des champs de la table
        unset($validatedData['author_ids'], $validatedData['term_ids']);

        // Mettez à jour l'enregistrement
        $record->update($validatedData);

        // Traitement des auteurs
        $author_ids = $request->input('author_ids', []);
        if (is_string($author_ids)) {
            $author_ids = explode(',', $author_ids);
        }
        if (isset($author_ids[0]) && is_string($author_ids[0])) {
            $author_ids = explode(',', $author_ids[0]);
        }

        $author_ids = array_filter(array_map('intval', $author_ids));

        if (empty($author_ids)) {
            return back()->withErrors(['author_ids' => 'Au moins un auteur doit être sélectionné.'])->withInput();
        }

        // Mettez à jour les relations entre les auteurs et l'enregistrement
        $record->authors()->sync($author_ids);

        // Traitement des termes du thésaurus
        $term_ids = $request->input('term_ids', []);
        if (is_string($term_ids)) {
            $term_ids = explode(',', $term_ids);
        }
        if (isset($term_ids[0]) && is_string($term_ids[0])) {
            $term_ids = explode(',', $term_ids[0]);
        }

        $term_ids = array_filter(array_map('intval', $term_ids));

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

        return redirect()->route('records.show', $record->id)->with('success', 'Record updated successfully.');
    }

    public function destroy(Record $record)
    {
        $this->authorize('delete', $record);

        $record->delete();

        return redirect()->route('records.index')->with('success', 'Record deleted successfully.');
    }

    // ici c\'est pour l'import export
    public function exportButton(Request $request)
    {
        // Vérifier les permissions d'export pour les records
        $this->authorize('export', Record::class);

        $recordIds = explode(',', $request->query('records'));
        $format = $request->query('format', 'excel');
        $records = Record::whereIn('id', $recordIds)->get();

        $slips = "";
        try {
            switch ($format) {
                case 'excel':
                    return Excel::download(new RecordsExport($records), 'records_export.xlsx');
                case 'ead':
                    $xml = $this->generateEAD($records);
                    return response($xml)
                        ->header('Content-Type', 'application/xml')
                        ->header('Content-Disposition', 'attachment; filename="records_export.xml"');
                case 'seda':
                    return $this->exportSEDA($records,$slips);
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
        $this->authorize('export', Record::class);

        $dollyId = $request->input('dolly_id');
        $format = $request->input('format');

        if ($dollyId) {
            $dolly = Dolly::findOrFail($dollyId);
            $records = $dolly->records;
            $slips = $dolly->slips;
        } else {
            $records = Record::all();
            $slips = Slip::all();
        }

        switch ($format) {
            case 'excel':
                return Excel::download(new RecordsExport($records), 'records.xlsx');
            case 'ead':
                $xml = $this->generateEAD($records);
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
        $this->authorize('import', Record::class);

        return view('records.import');
    }

    public function exportForm()
    {
        $this->authorize('export', Record::class);

        $dollies = Dolly::all();
        return view('records.export', compact('dollies'));
    }

    public function import(Request $request)
    {
        $this->authorize('import', Record::class);
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
            'type_id' => 1, // Assurez-vous d'avoir un type par défaut
        ]);

        try {
            switch ($format) {
                case 'excel':
                    Excel::import(new RecordsImport($dolly), $file);
                    break;
                case 'ead':
                    $this->importEAD($file, $dolly);
                    break;
                case 'seda':
                    $this->importSEDA($file, $dolly);
                    break;
            }
            return redirect()->route('records.index')->with('success', 'Records imported successfully and attached to new Dolly.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing records: ' . $e->getMessage());
        }
    }

    private function generateEAD($records)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
    <ead xmlns="urn:isbn:1-931666-22-9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:isbn:1-931666-22-9">
    </ead>');

        $eadheader = $xml->addChild('eadheader');
        $eadheader->addChild('eadid', 'YOUR_UNIQUE_ID');
        $filedesc = $eadheader->addChild('filedesc');
        $filedesc->addChild('titlestmt')->addChild('titleproper', 'Your Archive Title');

        $archdesc = $xml->addChild('archdesc');
        $archdesc->addAttribute('level', 'collection');
        $did = $archdesc->addChild('did');
        $did->addChild('unittitle', 'Your Collection Title');

        foreach ($records as $record) {
            $c = $archdesc->addChild('c');
            $c->addAttribute('level', $record->level->name ?? 'item');
            $c_did = $c->addChild('did');
            $c_did->addChild('unittitle', $record->name);
            $c_did->addChild('unitdate', $record->date_start)->addAttribute('normal', $record->date_start);
            $c_did->addChild('physdesc')->addChild('extent', $record->width_description);

            if ($record->content) {
                $c->addChild('scopecontent')->addChild('p', $record->content);
            }
        }

        // Format the XML with indentation
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        return $dom->saveXML();
    }

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
                        $newAttachment = new RecordAttachment([
                            'name' => $fileName,
                            'path' => 'attachments/' . $fileName,
                            'size' => (int)$attachment->Size,
                            'crypt' => (string)$attachment->Crypt,
                            'creator_id' => Auth::id(), // Assuming the current user is the creator
                        ]);

                        $newRecord->attachments()->save($newAttachment);

                        // Move file to the correct storage location
                        Storage::putFileAs('public/attachments', $filePath, $fileName);
                    }
                }
            }

            // Clean up temporary files
            Storage::deleteDirectory('temp_import');
        }
    }

    public function printRecords(Request $request)
    {
        $recordIds = $request->input('records');
        $records = Record::whereIn('id', $recordIds)->get();

        $pdf = PDF::loadView('records.print', compact('records'));
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
}
