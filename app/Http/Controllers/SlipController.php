<?php

namespace App\Http\Controllers;

use App\Exports\EADExport;
use App\Exports\SEDAExport;
use App\Exports\SlipsExport;
use App\Imports\SlipsImport;
use Illuminate\Http\Request;
use App\Models\Organisation;
use App\Models\Slip;
use App\Models\SlipStatus;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use SimpleXMLElement;


class SlipController extends Controller
{

    public function index()
    {
        $slips = Slip::all();
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

        Slip::create($request->all());

        return redirect()->route('slips.index')
            ->with('success', 'Slip created successfully.');
    }






    public function show(Slip $slip)
    {
        return view('transferrings.slips.show', compact('slip'));
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

        return redirect()->route('slip.index')
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
                            ->get();
                break;

            case 'received':
                $slips = Slip::where('is_received', '=', true)
                            ->whereNull('is_approved')
                            ->get();
                break;

            case 'approved':
                $slips = Slip::where('is_approved', '=', true)
                            ->get();
                break;
        }

        $slips->load('officer', 'officerOrganisation', 'userOrganisation', 'user','slipStatus','records');
        return view('transferrings.slips.index', compact('slips'));
    }


    public function export($format)
    {
        switch($format) {
            case 'excel':
                return Excel::download(new SlipsExport, 'slips.xlsx');
            case 'ead':
                return response($this->exportEAD())
                    ->header('Content-Type', 'text/xml')
                    ->header('Content-Disposition', 'attachment; filename="slips_ead.xml"');
            case 'seda':
                return response($this->exportSEDA())
                    ->header('Content-Type', 'text/xml')
                    ->header('Content-Disposition', 'attachment; filename="slips_seda.xml"');
            default:
                return redirect()->back()->with('error', 'Format non supporté');
        }
    }

    public function import(Request $request, $format)
    {
        $file = $request->file('file');
        $slips = Slip::with('user')->get();
        switch($format) {
            case 'excel':
                Excel::import(new SlipsImport, $file);
                break;
            case 'seda':
                $exporter = new SEDAExport();
                $content = $exporter->export($slips);
                $filename = 'export_seda_' . date('YmdHis') . '.xml';
                break;
            case 'ead':
                $exporter = new EADExport();
                $content = $exporter->export($slips);
                $filename = 'export_ead_' . date('YmdHis') . '.xml';
                break;
            default:
                return redirect()->back()->with('error', 'Format non supporté');
        }

        return redirect()->back()->with('success', 'Import réussi');
    }

    private function exportEAD()
    {
        $slips = Slip::all();

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><ead></ead>');

        foreach ($slips as $slip) {
            $c = $xml->addChild('c');
            $c->addChild('did')->addChild('unittitle', $slip->name);
            $c->did->addChild('unitid', $slip->code);
            $c->addChild('scopecontent')->addChild('p', $slip->description);
        }

        return $xml->asXML();
    }

    private function exportSEDA()
    {
        $slips = Slip::all();

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><ArchiveTransfer xmlns="fr:gouv:culture:archivesdefrance:seda:v2.1"></ArchiveTransfer>');

        foreach ($slips as $slip) {
            $archive = $xml->addChild('Archive');
            $archive->addChild('ArchiveObject')->addChild('Name', $slip->name);
            $archive->ArchiveObject->addChild('Description', $slip->description);
        }

        return $xml->asXML();
    }

    private function importEAD($file)
    {
        $xml = simplexml_load_file($file);

        foreach ($xml->xpath('//c') as $c) {
            Slip::create([
                'name' => (string)$c->did->unittitle,
                'code' => (string)$c->did->unitid,
                'description' => (string)$c->scopecontent->p,
                // ... autres champs ...
            ]);
        }
    }

    private function importSEDA($file)
    {
        $xml = simplexml_load_file($file);

        foreach ($xml->xpath('//Archive') as $archive) {
            Slip::create([
                'name' => (string)$archive->ArchiveObject->Name,
                'description' => (string)$archive->ArchiveObject->Description,
                // ... autres champs ...
            ]);
        }
    }
    public function importForm()
    {
        return view('transferrings.slips.import');
    }

}


