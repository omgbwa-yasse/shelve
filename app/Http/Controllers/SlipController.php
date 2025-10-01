<?php

namespace App\Http\Controllers;

use App\Exports\EADExport;
use App\Exports\SEDAExport;
use App\Exports\SlipExport;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\EADImportService;
use App\Services\SedaImportService;
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
        $currentOrganisation = Auth::user()->currentOrganisation;
        return view('slips.create', compact('organisations', 'users', 'slipStatuses', 'currentOrganisation'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:20',
            'name' => 'required|max:200',
            'description' => 'nullable',
            'user_organisation_id' => 'required|exists:organisations,id',
            'user_id' => 'nullable|exists:users,id',
            'is_received' => 'nullable|boolean',
            'received_date' => 'nullable|date',
            'is_approved' => 'nullable|boolean',
            'approved_date' => 'nullable|date',
        ]);

        // Ajouter automatiquement l'organisation courante de l'utilisateur et l'officer_id
        $request->merge([
            'officer_id' => Auth::id(),
            'officer_organisation_id' => Auth::user()->current_organisation_id,
        ]);

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

    $request->merge(['officer_id' => Auth::id()]);
        $request->merge(['organisation_id' => Auth::user()->current_organisation_id]);
        $request->merge(['slip_status_id' => 1]);
    $request->merge(['is_received' => false]);
        $request->merge(['received_date' => null]);
    $request->merge(['is_approved' => false]);
    $request->merge(['approved_date' => null]);

        $slip = Slip::create($request->all());

        $selectedMailContainers = $request->input('mail_containers');
        $containers = MailContainer::findOrFail($selectedMailContainers)->with('mails')->get();
        $mails = $containers->mails;

    // containers transfer handled elsewhere via pivot


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
                // 'container_id' removed: containers now managed via record_container pivot
                'creator_id' => Auth::id(),
            ]);
            $i++;
        }


        return redirect()->route('slips.index')
            ->with('success', 'Slip created successfully.');
    }



    public function storetransfert(Request $request)
    {
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

    $request->merge(['officer_id' => Auth::id()]);

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
                // container relationship migrated to pivot; skip single container_id
                'creator_id' => Auth::id(),
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
            'is_received' => true,
            'received_by' => Auth::id(),
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
                function ($_, $value, $fail) {
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
            'is_approved' => true,
            'approved_by' => Auth::id(),
            'approved_date' => now(),
        ]);

        return redirect()->route('slips.show',$slip)
            ->with('success', 'Slip received successfully.');

    }




    public function integrate(Request $request)
    {
    $request->validate([
            'id' => 'required|exists:slips,id',
        ]);

        $slip = Slip::updateOrCreate(
            ['id' => $request->input('id')],
            [
                'is_integrated' => true,
                'integrated_by' => Auth::id(),
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
                    // container_id removed; manage containers via pivot after creation if needed
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

    $request->merge(['officer_id' => Auth::id()]);

        $slip->update($request->all());

        return redirect()->route('slips.index')
            ->with('success', 'Slip updated successfully.');
    }




    public function destroy(Slip $slip)
    {
        // Vérifier s'il y a des slip_records associés
        $slipRecordsCount = $slip->records()->count();

        if ($slipRecordsCount > 0) {
            return redirect()->route('slips.index')
                ->with('error', "Impossible de supprimer le bordereau. Il contient {$slipRecordsCount} document(s). Veuillez vider le bordereau avant de le supprimer.");
        }

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
        // Récupérer l'organisation de l'utilisateur connecté
        $userOrganisationId = Auth::user()->current_organisation_id;

        // Récupérer uniquement les bordereaux émis ou reçus par l'organisation courante
        $slips = Slip::where(function($query) use ($userOrganisationId) {
            $query->where('officer_organisation_id', $userOrganisationId)
                ->orWhere('user_organisation_id', $userOrganisationId);
        })
        ->with(['officer', 'officerOrganisation', 'userOrganisation', 'user', 'slipStatus', 'records'])
        ->orderBy('created_at', 'desc')
        ->get();

        return view('slips.export', compact('slips'));
    }


    public function export(Request $request)
    {
        $slipId = $request->input('slip_id'); // Paramètre pour un slip spécifique
        $format = $request->input('format', 'excel');

        // Récupérer l'organisation de l'utilisateur connecté
        $userOrganisationId = Auth::user()->current_organisation_id;

        // Déterminer quel slip exporter
        if ($slipId) {
            // Exporter un slip spécifique
            $slip = Slip::where('id', $slipId)
                ->where(function($query) use ($userOrganisationId) {
                    $query->where('officer_organisation_id', $userOrganisationId)
                        ->orWhere('user_organisation_id', $userOrganisationId);
                })->firstOrFail();
        } else {
            // Prendre le premier bordereau de l'organisation
            $slip = Slip::where(function($query) use ($userOrganisationId) {
                $query->where('officer_organisation_id', $userOrganisationId)
                    ->orWhere('user_organisation_id', $userOrganisationId);
            })->first();

            if (!$slip) {
                return redirect()->back()->with('error', 'Aucun bordereau trouvé');
            }
        }

        // Charger toutes les relations nécessaires pour l'export
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
            'records.authors'
        ]);

        switch ($format) {
            case 'excel':
                return Excel::download(new SlipExport($slip), 'bordereau_' . $slip->code . '.xlsx');
            case 'ead':
                $ead = new \App\Exports\EADExport();
                $xml = $ead->export(collect([$slip]));
                return response($xml)
                    ->header('Content-Type', 'application/xml')
                    ->header('Content-Disposition', 'attachment; filename="bordereau_' . $slip->code . '.xml"');
            case 'seda':
                return $this->exportSEDA(collect([$slip]));
            default:
                return redirect()->back()->with('error', 'Format d\'export invalide');
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
                function ($_, $value, $fail) use ($format) {
                    $extension = strtolower($value->getClientOriginalExtension());
                    if ($format === 'excel' && $extension !== 'xlsx') {
                        $fail('The file must be an Excel file (.xlsx)');
                    } elseif ($format === 'ead' && $extension !== 'xml') {
                        $fail('The file must be an XML file (.xml)');
                    } elseif ($format === 'seda' && !in_array($extension, ['xml','zip'])) {
                        $fail('The file must be a SEDA XML (.xml) or package (.zip)');
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
            $service = new EADImportService();
            $service->importSlipsFromString(file_get_contents($file->getPathname()), $dolly);
                    break;
                case 'seda':
                    $service = new SedaImportService();
                    $ext = strtolower($file->getClientOriginalExtension());
                    if ($ext === 'zip') {
                        $service->importSlipFromZip($file->getPathname(), $dolly);
                    } else {
                        $service->importSlipFromString(file_get_contents($file->getPathname()), $dolly);
                    }
                    break;
                default:
                    return redirect()->back()->with('error', 'Invalid import format');
            }
            return redirect()->route('slips.index')
                ->with('success', 'Slips imported successfully and attached to new Dolly.');
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Import error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error importing slips: ' . $e->getMessage());
        }
    }


    // EAD/SEDA specific import logic is handled by dedicated services.


    // Legacy EAD2002 generator removed in favor of App\Exports\EADExport (EAD3)

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

    if ($zip->open(storage_path('app/public/' . $zipFileName), ZipArchive::CREATE) === true) {
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


