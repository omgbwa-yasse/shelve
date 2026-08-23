<?php

namespace App\Http\Controllers;

use App\Exports\SlipExport;
use App\Imports\SlipsImport;
use App\Models\Dolly;
use App\Models\SlipRecord;
use App\Models\SlipRecordAttachment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Organisation;
use App\Models\MailContainer;
use App\Models\Slip;
use App\Models\Record;
use App\Models\RecordStatus;
use App\Models\RecordPhysical;
use App\Models\SlipStatus;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\EADImportService;
use App\Services\SedaImportService;
use ZipArchive;



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
        $pdf = Pdf::loadView('slips.print', compact('slip'));

        // Configurer le PDF
        $pdf->setPaper('A4');

        // Nom du fichier
        $filename = 'bordereau_' . $slip->code . '.pdf';

        // Retourner le PDF pour téléchargement
        return $pdf->download($filename);
    }
    public function index()
    {
        $query = Slip::where('is_received', false)
            ->whereNotNull('code')
            ->whereNotNull('name')
            ->where('is_approved', false)
            ->where('is_integrated', false);

        if (!Auth::user()->isSuperAdmin()) {
            $query->forOrganisation(Auth::user()->current_organisation_id);
        }

        $slips = $query->paginate(10);
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
        $defaultStatus = \App\Models\SlipStatus::where('name', 'Projects')->first();
        $defaultStatusId = $defaultStatus ? $defaultStatus->id : 1;

        // Créer le slip avec toutes les données requises
        $slip = Slip::create([
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'user_organisation_id' => $request->user_organisation_id,
            'user_id' => $request->user_id,
            'officer_id' => Auth::user()->id,
            'officer_organisation_id' => Auth::user()->current_organisation_id,
            'slip_status_id' => $defaultStatusId,
            'is_received' => $request->is_received ?? false,
            'received_date' => $request->received_date,
            'is_approved' => $request->is_approved ?? false,
            'approved_date' => $request->approved_date,
        ]);

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

        $defaultStatus = \App\Models\SlipStatus::where('name', 'Projects')->first();
        $defaultStatusId = $defaultStatus ? $defaultStatus->id : 1;

        $slip = Slip::create([
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'officer_id' => Auth::id(),
            'officer_organisation_id' => Auth::user()->current_organisation_id,
            'user_organisation_id' => Auth::user()->current_organisation_id,
            'slip_status_id' => $defaultStatusId,
            'is_received' => false,
            'received_date' => null,
            'is_approved' => false,
            'approved_date' => null,
        ]);

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
            'user_organisation_id' => 'required|exists:organisations,id',
            'user_id' => 'nullable|exists:users,id',
            'slip_status_id' => 'nullable|exists:slip_statuses,id',
            'selected_records' => 'required|array',
        ]);

        $defaultStatus = \App\Models\SlipStatus::where('name', 'Projects')->first();
        $defaultStatusId = $defaultStatus ? $defaultStatus->id : 1;

        $slip = Slip::create([
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'officer_organisation_id' => Auth::user()->current_organisation_id,
            'officer_id' => Auth::id(),
            'user_organisation_id' => $request->user_organisation_id,
            'user_id' => $request->user_id,
            'slip_status_id' => $request->slip_status_id ?? $defaultStatusId,
        ]);

        foreach ($request->input('selected_records') as $recordId) {
            $record = RecordPhysical::findOrFail($recordId);

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



    public function reception(Slip $slip)
    {
        $this->authorize('update', $slip);
        $this->ensureDestinationCanAct($slip);

        DB::transaction(function () use ($slip) {
            $slip = Slip::query()->lockForUpdate()->findOrFail($slip->id);
            abort_if($slip->is_rejected || $slip->is_integrated, 409, 'Ce bordereau ne peut plus être réceptionné.');

            $slip->update([
                'is_received' => true,
                'received_by' => Auth::id(),
                'received_date' => now(),
            ]);

            $this->updateLinkedRecords($slip, $slip->transfer_type === 'archival_deposit' ? 'deposit_received' : 'transfer_received');
        });

        return redirect()->route('slips.show', $slip)
            ->with('success', 'Bordereau réceptionné par la direction destinataire.');
    }


    public function approve(Slip $slip)
    {
        $this->authorize('update', $slip);
        $this->ensureDestinationCanAct($slip);

        DB::transaction(function () use ($slip) {
            $slip = Slip::query()->lockForUpdate()->findOrFail($slip->id);
            abort_unless($slip->is_received && $slip->received_date, 409, 'Le bordereau doit être réceptionné avant approbation.');
            abort_if($slip->is_rejected || $slip->is_integrated, 409, 'Ce bordereau ne peut plus être approuvé.');

            $slip->update([
                'is_approved' => true,
                'approved_by' => Auth::id(),
                'approved_date' => now(),
            ]);

            $dateField = $slip->transfer_type === 'archival_deposit'
                ? 'deposit_approved_date'
                : 'transfer_approved_date';
            $this->updateLinkedRecords(
                $slip,
                $slip->transfer_type === 'archival_deposit' ? 'deposit_approved' : 'transfer_approved',
                [$dateField => now()->toDateString()]
            );
        });

        return redirect()->route('slips.show', $slip)
            ->with('success', 'Bordereau approuvé. Le mouvement peut être intégré.');
    }




    public function integrate(Slip $slip)
    {
        $this->authorize('update', $slip);
        $this->ensureDestinationCanAct($slip);

        DB::transaction(function () use ($slip) {
            $slip = Slip::query()->with('records.sourceRecord')->lockForUpdate()->findOrFail($slip->id);
            abort_unless($slip->is_received && $slip->is_approved, 409, 'Le bordereau doit être reçu et approuvé avant intégration.');
            abort_if($slip->is_rejected || $slip->is_integrated, 409, 'Ce bordereau ne peut plus être intégré.');

            foreach ($slip->records as $source) {
                $record = $source->sourceRecord
                    ?? Record::query()
                        ->where('code', $source->code)
                        ->where('organisation_id', $slip->officer_organisation_id)
                        ->first();

                abort_unless($record, 409, "La notice source {$source->code} n'est pas reliée au bordereau.");

                if (! $source->record_id) {
                    $source->update(['record_id' => $record->id]);
                }

                $changes = [
                    'organisation_id' => $slip->user_organisation_id,
                    'archival_status_gvaa' => $slip->transfer_type === 'archival_deposit' ? 'deposited' : 'transferred',
                ];

                if ($slip->transfer_type === 'archival_deposit') {
                    $changes['deposit_effective_date'] = now()->toDateString();
                    $changes['status_id'] = RecordStatus::where('name', 'Archivé')->value('id') ?? $record->status_id;
                } else {
                    $changes['transfer_effective_date'] = now()->toDateString();
                }

                $record->update($changes);
            }

            $slip->update([
                'is_integrated' => true,
                'integrated_by' => Auth::id(),
                'integrated_date' => now(),
            ]);
        });

        return redirect()->route('slips.show', $slip)
            ->with('success', 'Mouvement intégré : la direction détentrice et les dates du cycle de vie ont été mises à jour.');
    }

    public function reject(Request $request, Slip $slip)
    {
        $this->authorize('update', $slip);
        $this->ensureDestinationCanAct($slip);
        $request->validate(['reason' => 'required|string|max:2000']);

        abort_if($slip->is_integrated, 409, 'Un bordereau intégré ne peut pas être rejeté.');

        $slip->update([
            'is_rejected' => true,
            'rejected_by' => Auth::id(),
            'rejected_date' => now(),
            'rejection_reason' => $request->input('reason'),
        ]);

        $this->updateLinkedRecords($slip, $slip->transfer_type === 'archival_deposit' ? 'deposit_rejected' : 'transfer_rejected');

        return redirect()->route('slips.show', $slip)->with('success', 'Bordereau rejeté et renvoyé au service versant.');
    }

    public function resubmit(Slip $slip)
    {
        $this->authorize('update', $slip);
        abort_unless(
            Auth::user()->isSuperAdmin()
                || (int) Auth::user()->current_organisation_id === (int) $slip->officer_organisation_id,
            403
        );
        abort_unless($slip->is_rejected, 409, 'Seul un bordereau rejeté peut être resoumis.');

        $slip->update([
            'is_received' => false,
            'received_by' => null,
            'received_date' => null,
            'is_approved' => false,
            'approved_by' => null,
            'approved_date' => null,
            'is_rejected' => false,
            'rejected_by' => null,
            'rejected_date' => null,
            'rejection_reason' => null,
        ]);

        $this->updateLinkedRecords($slip, $slip->transfer_type === 'archival_deposit' ? 'deposit_pending' : 'transfer_pending');

        return redirect()->route('slips.show', $slip)->with('success', 'Bordereau corrigé et resoumis.');
    }

    private function ensureDestinationCanAct(Slip $slip): void
    {
        abort_unless(
            Auth::user()->isSuperAdmin()
                || (int) Auth::user()->current_organisation_id === (int) $slip->user_organisation_id,
            403,
            'Cette action appartient à la direction destinataire.'
        );
    }

    private function updateLinkedRecords(Slip $slip, string $archivalStatus, array $extra = []): void
    {
        $recordIds = $slip->records()->whereNotNull('record_id')->pluck('record_id');
        if ($recordIds->isNotEmpty()) {
            Record::whereIn('id', $recordIds)->update(array_merge(['archival_status_gvaa' => $archivalStatus], $extra));
        }
    }



    public function show(Slip $slip)
    {
        $this->authorize('view', $slip);
        $slip->load('records.level', 'records.support', 'records.activity', 'records.containers', 'records.creator', 'records.sourceRecord');
        $slipRecords = $slip->records;
        return view('slips.show', compact('slip', 'slipRecords'));
    }




    public function edit(Slip $slip)
    {
        $this->authorize('update', $slip);
        $organisations = Organisation::all();
        $users = User::all();
        $slipStatuses = SlipStatus::all();
        return view('slips.edit', compact('slip', 'organisations', 'users', 'slipStatuses'));
    }



    public function update(Request $request, Slip $slip)
    {
        $this->authorize('update', $slip);

        $request->validate([
            'code' => 'required|max:20',
            'name' => 'required|max:200',
            'description' => 'nullable',
            'user_organisation_id' => 'required|exists:organisations,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        // A correction must not silently change the emitter, the workflow
        // status or any reception/approval dates. Those fields are controlled
        // exclusively by the lifecycle actions (receive, reject, resubmit...).
        $slip->update([
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'user_organisation_id' => $request->user_organisation_id,
            'user_id' => $request->user_id,
        ]);

        return redirect()->route('slips.show', $slip)
            ->with('success', 'Bordereau corrigé. Vous pouvez maintenant le resoumettre.');
    }




    public function destroy(Slip $slip)
    {
        $this->authorize('delete', $slip);

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

        $baseQuery = Slip::query();

        // Apply org scoping for non-superadmin users
        if (!Auth::user()->isSuperAdmin()) {
            $baseQuery->forOrganisation(Auth::user()->current_organisation_id);
        }

        switch ($type) {
            case 'project':
                $slips = (clone $baseQuery)->where('is_received', '=', false)
                            ->where('is_approved', '=', false)
                            ->paginate(10);
                break;

            case 'received':
                $slips = (clone $baseQuery)->where('is_received', '=', true)
                            ->whereNull('is_approved')
                            ->paginate(10);
                break;

            case 'approved':
                $slips = (clone $baseQuery)->where('is_approved', '=', true)
                            ->paginate(10);
                break;

            case 'integrated':
                $slips = (clone $baseQuery)->where('is_integrated', '=', true)
                            ->paginate(10);
                break;

            default:
                $slips = (clone $baseQuery)->where('is_received', false)
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
