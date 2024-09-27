<?php

namespace App\Http\Controllers;

use App\Exports\EADExport;
use App\Exports\SEDAExport;
use App\Exports\SlipsExport;
use App\Imports\SlipsImport;
use App\Models\Dolly;
use Illuminate\Http\Request;
use App\Models\Organisation;
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

    public function index()
    {
        $slips = Slip::where('is_received', false)
            ->where('is_approved', false)
            ->where('is_integrated', false)
            ->paginate(10);
        return view('transferrings.slips.index', compact('slips'));
    }



    public function create()
    {
        $organisations = Organisation::all();
        $users = User::all();
        $slipStatuses = SlipStatus::all();
        return view('transferrings.slips.create', compact('organisations', 'users', 'slipStatuses'));
    }



    public function store(Request $request)
    {
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
        $slip->load('records.level', 'records.support', 'records.activity', 'records.container', 'records.creator');
        $slipRecords = $slip->records;
        return view('transferrings.slips.show', compact('slip', 'slipRecords'));
    }




    public function edit(Slip $slip)
    {
        $organisations = Organisation::all();
        $users = User::all();
        $slipStatuses = SlipStatus::all();
        return view('transferrings.slips.edit', compact('slip', 'organisations', 'users', 'slipStatuses'));
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
        return view('transferrings.slips.index', compact('slips'));
    }
    public function exportForm()
    {
        $dollies = Dolly::all();
        return view('transferrings.slips.export', compact('dollies'));
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
                    Excel::import(new SlipsImport($dolly), $file);
                    break;
                case 'ead':
                    $this->importEAD($file, $dolly);
                    break;
                case 'seda':
                    $this->importSEDA($file, $dolly);
                    break;
            }
            return redirect()->route('slips.index')->with('success', 'Slips imported successfully and attached to new Dolly.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing slips: ' . $e->getMessage());
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
                // Map other fields as needed
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
                // Map other fields as needed
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
        return view('transferrings.slips.import');
    }

}


