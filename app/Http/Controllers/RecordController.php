<?php

namespace App\Http\Controllers;
use App\Exports\RecordsExport;
use App\Imports\RecordsImport;
use Illuminate\Support\Facades\Gate;

use App\Models\Attachment;
use App\Models\Dolly;
use App\Models\Organisation;
use App\Models\Record;
use App\Models\RecordSupport;
use App\Models\RecordStatus;
use App\Models\Container;
use App\Models\Activity;
use App\Models\Slip;
use App\Models\Term;
use App\Models\Accession;
use App\Models\Author;
use App\Models\RecordLevel;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

use ZipArchive;


class RecordController extends Controller
{

    public function index()
    {
        $records = Record::with([
            'level',
            'status',
            'support',
            'activity',
//            'container',
            'authors',
            'terms'
        ])->paginate(10);

        $statuses = RecordStatus::all();
        $terms = Term::all();
        $users = User::select('id', 'name')->get();
        $organisations = Organisation::select('id', 'name')->get();

        return view('records.index', compact(
            'records',
            'statuses',
            'terms',
            'users',
            'organisations'
        ));
    }


    public function create()
    {
        $statuses = RecordStatus::all();
        $supports = RecordSupport::all();
        $activities = Activity::all();
        $parents = Record::all();
        $containers = Container::all();
        $users = User::all();
        $levels = RecordLevel::all();
        $records = Record::all();
        $authors = Author::with('authorType')->get();
        $terms = Term::all();
        return view('records.create', compact('records','terms','authors','levels','statuses', 'supports', 'activities', 'parents', 'containers', 'users'));
    }


    public function store(Request $request)
    {

        $dateFormat = $this->getDateFormat($request->date_start, $request->date_end);
        if (strlen($dateFormat) > 1) {
            return back()->withErrors(['date_format' => 'The date format must not be greater than 1 character.'])->withInput();
        }

        $request->merge(['date_format' => $dateFormat ]);
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
            'author_ids' => 'required|array',
            'term_ids' => 'required|array',
        ]);

        $record = Record::create($validatedData);

        $term_ids = $request->input('term_ids');
        $author_ids = $request->input('author_ids');
        $term_ids = explode(',', $term_ids[0]);

        $author_ids = explode(',', $author_ids[0]);

        $term_ids = array_map('intval', $term_ids);
        $author_ids = array_map('intval', $author_ids);


        foreach ($author_ids as $author_id) {
            $record->authors()->attach($author_id);
        }

        foreach ($term_ids as $term_id) {
            $record->terms()->attach($term_id);
        }

        return redirect()->route('records.index')->with('success', 'Record created successfully.');
    }







    private function getDateFormat($dateStart, $dateEnd)
    {
        $start = new \DateTime($dateStart);
        $end = new \DateTime($dateEnd);

        if ($start->format('Y') !== $end->format('Y')) {
            return 'Y';
        } elseif ($start->format('m') !== $end->format('m')) {
            return 'M';
        } elseif ($start->format('d') !== $end->format('d')) {
            return 'D';
        }
        return 'D';
    }




    public function show(Record $record)
    {
       Gate::authorize('show', $record);
        $record->load('children');  // Charge les enregistrements enfants
        return view('records.show', compact('record'));
    }


    public function edit(Record $record)
    {
        Gate::authorize('edit', $record);

        $authors = Author::with('authorType')->get();
        $statuses = RecordStatus::all();
        $supports = RecordSupport::all();
        $activities = Activity::all();
        $parents = Record::all();
        $containers = Container::all();
        $users = User::all();
        $levels = RecordLevel::all();
        $terms = Term::all();


        $author_ids = $record->authors->pluck('id')->toArray();
        $term_ids = $record->terms->pluck('id')->toArray();

        return view('records.edit', compact('levels', 'record', 'statuses', 'supports', 'activities', 'parents', 'containers', 'users', 'authors', 'author_ids', 'terms', 'term_ids'));
    }

    public function update(Request $request, Record $record)
    {
        Gate::authorize('update', $record);

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
            'author_ids' => 'required|array',
            'term_ids' => 'required|array',
        ]);

        // Mettez à jour l'enregistrement
        $record->update($validatedData);
        // Supprimez les clés author_ids et term_ids du tableau $validatedData
        $term_ids = $request->input('term_ids');
        $author_ids = $request->input('author_ids');
        $term_ids = explode(',', $term_ids[0]);

        $author_ids = explode(',', $author_ids[0]);
        // Supprimez les valeurs vides du tableau
        //        $term_ids = array_filter($term_ids);
        //        $author_ids = array_filter($author_ids);

        // Convertissez les valeurs en entiers

        $term_ids = array_map('intval', $term_ids);
        $author_ids = array_map('intval', $author_ids);

        // Mettez à jour les relations entre les auteurs et l'enregistrement
        $record->authors()->sync($term_ids);

        // Mettez à jour les relations entre les termes et l'enregistrement
        $record->terms()->sync($author_ids);

        return redirect()->route('records.index')->with('success', 'Record updated successfully.');
    }

    public function destroy(Record $record)
    {
        $record->delete();

        return redirect()->route('records.index')->with('success', 'Record deleted successfully.');
    }


    // ici c'est pour l'import export
    public function exportButton(Request $request)
    {
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
            \Log::error('Erreur lors de l\'exportation: ' . $e->getMessage());
            return response()->json(['error' => 'Une erreur est survenue lors de l\'exportation.'], 500);
        }
    }


    public function export(Request $request)
    {
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
        return view('records.import');
    }
    public function exportForm()
    {
        $dollies = Dolly::all();
        return view('records.export', compact('dollies'));
    }

    public function import(Request $request)
    {
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
                            'creator_id' => auth()->id(), // Assuming the current user is the creator
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
        //    public function export(Request $request)
        //    {
        //        $recordIds = explode(',', $request->query('records'));
        //        $records = Record::whereIn('id', $recordIds)->get();
        //
        //        return Excel::download(new RecordsExport($records), 'records_export.xlsx');
        //    }

    public function printRecords(Request $request)
    {
        $recordIds = $request->input('records');
        $records = Record::whereIn('id', $recordIds)->get();

        $pdf = PDF::loadView('records.print', compact('records'));
        return $pdf->download('records_print.pdf');
    }
}
