<?php

namespace App\Http\Controllers;

use App\Exports\RecordsExport;
use App\Exports\SlipExport;
use App\Exports\SEDAExport;
use App\Exports\EADExport;
use App\Models\Dolly;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class DollyExportController extends Controller
{
    public function showExportForm()
    {
        // Récupérer l'organisation de l'utilisateur connecté
        $userOrganisationId = Auth::user()->current_organisation_id;

        // Récupérer uniquement les dollies de l'organisation courante ou publics
        $dollies = Dolly::where(function ($query) use ($userOrganisationId) {
            $query->where('owner_organisation_id', $userOrganisationId)
                  ->orWhere('is_public', true);
        })->get();

        return view('exports.dolly_export', compact('dollies'));
    }

    public function export(Request $request)
    {
        $request->validate([
            'dolly_id' => 'required|exists:dollies,id',
            'export_type' => 'required|in:records,slips',
            'export_format' => 'required|in:excel,seda,ead',
        ]);

        $dolly = Dolly::findOrFail($request->dolly_id);
        $items = ($request->export_type === 'records') ? $dolly->records : $dolly->slips;
        $fileName = $dolly->name . '_' . $request->export_type . '_' . now()->format('Y-m-d');

        switch ($request->export_format) {
            case 'excel':
                return $this->exportExcel($items, $fileName, $request->export_type);
            case 'seda':
                return $this->exportSEDA($items, $fileName);
            case 'ead':
                return $this->exportEAD($items, $fileName);
            default:
                return redirect()->back()->with('error', 'Format d\'export invalide');
        }
    }

    private function exportExcel($items, $fileName, $type)
    {
        if ($type === 'records') {
            $export = new RecordsExport($items);
        } else {
            // Pour les slips, prendre le premier slip de la collection
            $firstSlip = $items->first();
            if (!$firstSlip) {
                throw new \Exception('Aucun bordereau trouvé pour l\'export');
            }
            $export = new SlipExport($firstSlip);
            $fileName = 'bordereau_' . $firstSlip->code;
        }
        return Excel::download($export, $fileName . '.xlsx');
    }

    private function exportSEDA($items, $fileName)
    {
        $sedaExport = new SEDAExport();
        $xml = $sedaExport->export($items);
        return response($xml)
            ->header('Content-Type', 'application/xml')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '.xml"');
    }

    private function exportEAD($items, $fileName)
    {
        $eadExport = new EADExport();
        $xml = $eadExport->export($items);
        return response($xml)
            ->header('Content-Type', 'application/xml')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '.xml"');
    }
}
