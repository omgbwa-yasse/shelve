<?php

namespace App\Http\Controllers;

use App\Exports\EADExport;
use App\Exports\SEDAExport;
use App\Exports\SlipsExport;
use App\Imports\SlipsImport;
use App\Models\Dolly;
use App\Models\SlipRecord;
use App\Models\slipRecordAttachment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Organisation;
use App\Models\MailContainer;
use App\Models\Slip;
use App\Models\Record;
use App\Models\SlipStatus;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use SimpleXMLElement;
use ZipArchive;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Validator;



class SlipController extends Controller
{

    public function print(Slip $slip)
    {
        // Charger toutes les relations nécessaires
        $slip->load([
            'officerOrganisation',
            'officer',
            'userOrganisation',
            'user',
            'slipStatus',
            'records.level',
            'records.support',
            'records.activity',
            'records.containers',
            'records.creator',
            'records.attachments',
            'receivedAgent',
            'approvedAgent',
            'integratedAgent'
        ]);

        // Générer le PDF
        $pdf = PDF::loadView('slips.print', compact('slip'));

        // Configurer le PDF
        $pdf->setPaper('A4');

        // Nom du fichier
        $filename = 'bordereau_' . $slip->code . '.pdf';

        // Retourner le PDF pour téléchargement
        return $pdf->download($filename);
    }
    public function index()
    {
        $slips = Slip::where('is_received', false)
            ->whereNotNull('code')
            ->whereNotNull('name')
            ->where('is_approved', false)
            ->where('is_integrated', false)
            ->paginate(10);
        return view('slips.index', compact('slips'));
    }



    public function create()
    {
        $organisations = Organisation::all();
        $users = User::with('organisations')->get();
        $slipStatuses = SlipStatus::all();
        return view('slips.create', compact('organisations', 'users', 'slipStatuses'));
    }


    public function store(Request $request)
    {
//        dd($request);
        $request->validate([
            'code' => 'required|max:20',
            'name' => 'required|max:200',
            'description' => 'nullable',
            'officer_organisation_id' => 'required|exists:organisations,id', // supprimer
            'user_organisation_id' => 'required|exists:organisations,id',  // Supprimer et merge
            'user_id' => 'nullable|exists:users,id', // Supprimer et merge
            'slip_status_id' => 'required|exists:slip_statuses,id',
            'is_received' => 'nullable|boolean',
            'received_date' => 'nullable|date',
            'is_approved' => 'nullable|boolean',
            'approved_date' => 'nullable|date',
        ]);

        $request->merge(['officer_id' => auth()->user()->id]);

        Slip::create($request->all());

        return redirect()->route('slips.index')
            ->with('success', 'Slip created successfully.');
    }



    public function mailArchiving(Request $request)
    {
        $request->validate([
            'code' => 'required|max:20',
            'name' => 'required|max:200',
            'description' => 'nullable',
            'mail_containers' => 'required|array',
        ]);

        $request->merge(['officer_id' => auth()->user()->id]);
        $request->merge(['organisation_id' => auth()->user()->current_organisation_id]);
        $request->merge(['slip_status_id' => 1]);
        $request->merge(['is_received' => False]);
        $request->merge(['received_date' => null]);
        $request->merge(['is_approved' => false]);
        $request->merge(['approved_date' => NULL]);

        $slip = Slip::create($request->all());

        $selectedMailContainers = $request->input('mail_containers');
        $containers = MailContainer::findOrFail($selectedMailContainers)->with('mails')->get();
        $mails = $containers->mails;

        $container = Container::findOrFail(); // transferer les containers


        foreach( $mails as $mail){
            $i = 0;
            SlipRecord::create([
                'slip_id' => $slip->id,
                'code' => $slip->code.'-'.$i,
                'name' => $mail->name,
                'date_format' => 'D',
                'date_exact' => $mail->date,
                'content' => $mail->description,
                'level_id' => 1,
                'width' => 1,
                'width_description' => 1,
                'support_id' => 1,
                'activity_id' => 1,
                'container_id' => 1,
                'creator_id' => auth()->user()->id,
            ]);
            $i++;
        }


        return redirect()->route('slips.index')
            ->with('success', 'Slip created successfully.');
    }



    public function storetransfert(Request $request)
    {
//        dd($request);
        $request->validate([
            'code' => 'required|max:20',
            'name' => 'required|max:200',
            'description' => 'nullable',
            'officer_organisation_id' => 'required|exists:organisations,id',
            'user_organisation_id' => 'required|exists:organisations,id',
            'user_id' => 'nullable|exists:users,id',
            'slip_status_id' => 'required|exists:slip_statuses,id',
            'selected_records' => 'required|array',
        ]);

        $request->merge(['officer_id' => auth()->user()->id]);

        $slip = Slip::create($request->all());

        foreach ($request->input('selected_records') as $recordId) {
            $record = Record::findOrFail($recordId);

            // Mettre à jour le statut du record à 0
            $record->update(['status_id' => 0]);

            $slipRecord = SlipRecord::create([
                'slip_id' => $slip->id,
                'code' => $record->code,
                'name' => $record->name,
                'date_format' => $record->date_format,
                'date_start' => $record->date_start,
                'date_end' => $record->date_end,
                'date_exact' => $record->date_exact,
                'content' => $record->content,
                'level_id' => $record->level_id,
                'width' => $record->width,
                'width_description' => $record->width_description,
                'support_id' => $record->support_id,
                'activity_id' => $record->activity_id,
                'container_id' => $record->container_id,
                'creator_id' => auth()->id(),
            ]);

            foreach ($record->attachments as $attachment) {
                SlipRecordAttachment::create([
                    'slip_record_id' => $slipRecord->id,
                    'attachment_id' => $attachment->id,
                ]);
            }
        }

        return redirect()->route('slips.index')->with('success', 'Slip created successfully.');
    }



    public function reception(Request $request)
    {

        $request->validate([
            'id' => 'required|exists:slips,id',
        ]);

        $slip = Slip::findOrFail($request->input('id'));
        $slip->update([
            'is_received' => TRUE,
            'received_by' => auth()->id(),
            'received_date' => now(),
        ]);

        return redirect()->route('slips.index')
            ->with('success', 'Slip received successfully.');
    }


    public function approve(Request $request){

        $request->validate([
            'id' => [
                'required',
                'exists:slips,id',
                function ($attribute, $value, $fail) {
                    $slip = Slip::find($value);
                    if (!$slip) {
                        $fail('Le slip spécifié n\'existe pas.');
                    } elseif (!$slip->is_received) {
                        $fail('Le slip sélectionné n\'a pas encore été reçu.');
                    } elseif (empty($slip->received_date)) {
                        $fail('La date de réception du slip n\'est pas définie.');
                    }
                },
            ],
        ]);



        $slip = Slip::findOrFail($request->input('id'));

        $slip->update([
            'is_approved' => TRUE,
            'approved_by' => auth()->id(),
            'approved_date' => now(),
        ]);

        return redirect()->route('slips.show',$slip)
            ->with('success', 'Slip received successfully.');

    }




    public function integrate(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:slips,id',
        ]);

        $slip = Slip::updateOrCreate(
            ['id' => $request->input('id')],
            [
                'is_integrated' => true,
                'integrated_by' => auth()->id(),
                'integrated_date' => now(),
            ]
        );

        if ($slip) {
            foreach ($slip->records as $source) {

                $record = Record::create([
                    'code' => $source->code,
                    'name' => $source->name,
                    'date_format' => $source->date_format,
                    'date_start' => $source->date_start,
                    'date_end' => $source->date_end,
                    'date_exact' => $source->date_exact,
                    'content' => $source->content,
                    'level_id' => $source->level_id,
                    'width' => $source->width,
                    'width_description' => $source->width_description,
                    'support_id' => $source->support_id,
                    'activity_id' => $source->activity_id,
                    'container_id' => $source->container_id,
                    'user_id' => $source->creator_id,
                    'status_id' => 1,
                ]);
                if($source->authors){
                    $record->authors()->attach($source->authors->pluck('id'));
                }

            }
        } else {
            return back()->withErrors(['error' => 'Failed to integrate slip.']);
        }

        return redirect()->route('slips.show', $slip)
            ->with('success', 'Slip integrated successfully.');
    }



    public function show(Slip $slip)
    {
        $slip->load('records.level', 'records.support', 'records.activity', 'records.containers', 'records.creator');
        $slipRecords = $slip->records;
        return view('slips.show', compact('slip', 'slipRecords'));
    }




    public function edit(Slip $slip)
    {
        $organisations = Organisation::all();
        $users = User::all();
        $slipStatuses = SlipStatus::all();
        return view('slips.edit', compact('slip', 'organisations', 'users', 'slipStatuses'));
    }



    public function update(Request $request, Slip $slip)
    {
        $request->validate([
            'code' => 'required|max:20',
            'name' => 'required|max:200',
            'description' => 'nullable',
            'officer_organisation_id' => 'required|exists:organisations,id',
            'user_organisation_id' => 'required|exists:organisations,id',
            'user_id' => 'nullable|exists:users,id',
            'slip_status_id' => 'required|exists:slip_statuses,id',
            'is_received' => 'nullable|boolean',
            'received_date' => 'nullable|date',
            'is_approved' => 'nullable|boolean',
            'approved_date' => 'nullable|date',
        ]);

        $request->merge(['officer_id' => auth()->user()->id]);

        $slip->update($request->all());

        return redirect()->route('slips.index')
            ->with('success', 'Slip updated successfully.');
    }




    public function destroy(Slip $slip)
    {
        $slip->delete();

        return redirect()->route('slips.index')
            ->with('success', 'Slip deleted successfully.');
    }


    public function sort(Request $request)
    {
        $type = $request->input('categ');
        $slips = [];

        switch ($type) {
            case 'project':
                $slips = Slip::where('is_received', '=', false)
                            ->where('is_approved', '=', false)
                            ->paginate(10);
                break;

            case 'received':
                $slips = Slip::where('is_received', '=', true)
                            ->whereNull('is_approved')
                            ->paginate(10);
                break;

            case 'approved':
                $slips = Slip::where('is_approved', '=', true)
                            ->paginate(10);
                break;

            case 'integrated':
                $slips = Slip::where('is_integrated', '=', true)
                            ->paginate(10);
                break;

            default:
                $slips = Slip::where('is_received', false)
                            ->where('is_approved', false)
                            ->paginate(10);
                break;


        }

        $slips->load('officer', 'officerOrganisation', 'userOrganisation', 'user','slipStatus','records');
        return view('slips.index', compact('slips'));
    }
    public function exportForm()
    {
        $dollies = Dolly::all();
        return view('slips.export', compact('dollies'));
    }


    public function export(Request $request)
    {
        $dollyId = $request->input('dolly_id');
        $format = $request->input('format');

        if ($dollyId) {
            $dolly = Dolly::findOrFail($dollyId);
            $slips = $dolly->slips;
        } else {
            $slips = Slip::all();
        }

        switch ($format) {
            case 'excel':
                return Excel::download(new SlipsExport($slips), 'slips.xlsx');
            case 'ead':
                $xml = $this->generateEAD($slips);
                return response($xml)
                    ->header('Content-Type', 'application/xml')
                    ->header('Content-Disposition', 'attachment; filename="slips.xml"');
            case 'seda':
                return $this->exportSEDA($slips);
            default:
                return redirect()->back()->with('error', 'Invalid export format');
        }
    }

    public function import($format, Request $request)
    {
        // Validate the format parameter from the route
        if (!in_array($format, ['excel', 'ead', 'seda'])) {
            return redirect()->back()->with('error', 'Invalid import format');
        }

        // Validate the file upload
        $request->validate([
            'file' => [
                'required',
                'file',
                function ($attribute, $value, $fail) use ($format) {
                    $extension = strtolower($value->getClientOriginalExtension());
                    if ($format === 'excel' && $extension !== 'xlsx') {
                        $fail('The file must be an Excel file (.xlsx)');
                    } elseif (($format === 'ead' || $format === 'seda') && $extension !== 'xml') {
                        $fail('The file must be an XML file (.xml)');
                    }
                },
            ],
        ]);

        $file = $request->file('file');

        // Créer un nouveau Dolly
        $dolly = Dolly::create([
            'name' => 'Import ' . now()->format('Y-m-d H:i:s'),
            'description' => 'Imported data',
            'type_id' => 1,
        ]);

        try {
            switch ($format) {
                case 'excel':
                    Excel::import(new SlipsImport($dolly), $file);
                    break;
                case 'ead':
                    $this->importEAD($file, $dolly);
                    break;
                case 'seda':
                    $this->importSEDA($file, $dolly);
                    break;
            }
            return redirect()->route('slips.index')
                ->with('success', 'Slips imported successfully and attached to new Dolly.');
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Import error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error importing slips: ' . $e->getMessage());
        }
    }


    private function importEAD($file, $dolly)
    {
        $xml = simplexml_load_file($file);
        $xml->registerXPathNamespace('ead', 'urn:isbn:1-931666-22-9');

        $slips = $xml->xpath('//ead:c');

        foreach ($slips as $slip) {
            $data = [
                'name' => (string)$slip->did->unittitle,
                'code' => (string)$slip->did->unitid,
                'description' => (string)$slip->scopecontent->p,

            ];

            $newSlip = Slip::create($data);
            $dolly->slips()->attach($newSlip->id);
        }
    }

    private function importSEDA($file, $dolly)
    {
        $xml = simplexml_load_file($file);
        $xml->registerXPathNamespace('seda', 'fr:gouv:culture:archivesdefrance:seda:v2.1');

        $slips = $xml->xpath('//seda:ArchiveObject');

        foreach ($slips as $slip) {
            $data = [
                'name' => (string)$slip->Name,
                'description' => (string)$slip->Description,
            ];

            $newSlip = Slip::create($data);
            $dolly->slips()->attach($newSlip->id);
        }
    }


    private function generateEAD($slips)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><ead xmlns="urn:isbn:1-931666-22-9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:isbn:1-931666-22-9 "></ead>');

        $eadheader = $xml->addChild('eadheader');
        $eadheader->addChild('eadid', 'YOUR_UNIQUE_ID');
        $filedesc = $eadheader->addChild('filedesc');
        $filedesc->addChild('titlestmt')->addChild('titleproper', 'Your Archive Title');

        $archdesc = $xml->addChild('archdesc');
        $archdesc->addAttribute('level', 'collection');
        $did = $archdesc->addChild('did');
        $did->addChild('unittitle', 'Your Collection Title');

        foreach ($slips as $slip) {
            $c = $archdesc->addChild('c');
            $c->addAttribute('level', 'item');
            $c_did = $c->addChild('did');
            $c_did->addChild('unittitle', $slip->name);
            $c_did->addChild('unitid', $slip->code);
            $c_did->addChild('unitdate', $slip->created_at->format('Y-m-d'));
            $c_did->addChild('physdesc')->addChild('extent', '1 slip');

            if ($slip->description) {
                $c->addChild('scopecontent')->addChild('p', $slip->description);
            }
        }

        // Format the XML with indentation
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        return $dom->saveXML();
    }

    private function exportSEDA($slips)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><ArchiveTransfer xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="fr:gouv:culture:archivesdefrance:seda:v2.1 seda-2.1-main.xsd" xmlns="fr:gouv:culture:archivesdefrance:seda:v2.1"></ArchiveTransfer>');

        $xml->addChild('Comment', 'Archive Transfer');
        $xml->addChild('Date', date('Y-m-d'));

        $archive = $xml->addChild('Archive');

        foreach ($slips as $slip) {
            $archiveObject = $archive->addChild('ArchiveObject');
            $archiveObject->addChild('Name', $slip->name);
            $archiveObject->addChild('Description', $slip->description);

            $document = $archiveObject->addChild('Document');
            $document->addChild('Identification', $slip->code);
            $document->addChild('Type', 'Slip');

            // Ajoutez ici la logique pour inclure les pièces jointes si nécessaire
        }

        // Format the XML with indentation
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        $formattedXml = $dom->saveXML();

        $zipFileName = 'slips_seda_export_' . time() . '.zip';
        $zip = new ZipArchive();

        if ($zip->open(storage_path('app/public/' . $zipFileName), ZipArchive::CREATE) === TRUE) {
            $zip->addFromString('slips.xml', $formattedXml);

            // Ajoutez ici la logique pour inclure les pièces jointes si nécessaire

            $zip->close();
        }

        return response()->download(storage_path('app/public/' . $zipFileName))->deleteFileAfterSend(true);
    }



    public function importForm()
    {
        return view('slips.import');
    }

}


