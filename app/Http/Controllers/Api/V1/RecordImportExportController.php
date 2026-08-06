<?php

namespace App\Http\Controllers\Api\V1;

use App\Exports\EADExport;
use App\Exports\SEDAExport;
use App\Exports\UnifiedRecordsExport;
use App\Models\Dolly;
use App\Models\Record;
use App\Services\RecordsBulkImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Import / Export en masse des notices (Records) — choisir un format puis
 * exporter (Excel/SEDA/EAD) ou importer (Excel). Écran Next
 * `/records/import` et `/records/export`.
 */
class RecordImportExportController extends Controller
{
    /**
     * GET /api/v1/records/export?format=excel|seda|ead
     */
    public function export(Request $request): Response
    {
        $format = $request->query('format', 'excel');

        $records = Record::inOrganisation(Auth::user()->current_organisation_id)
            ->currentVersion()
            ->get();

        return match ($format) {
            'seda' => $this->xmlResponse(
                (new SEDAExport())->exportRecords($records),
                'notices-seda.xml',
            ),
            'ead' => $this->xmlResponse(
                (new EADExport())->exportRecords($records),
                'notices-ead.xml',
            ),
            default => Excel::download(new UnifiedRecordsExport($records), 'notices.xlsx'),
        };
    }

    /**
     * POST /api/v1/records/import (multipart : file)
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $service = new RecordsBulkImportService(
            Auth::user()->current_organisation_id,
            Auth::id(),
        );

        $report = $service->import($request->file('file'));

        return response()->json([
            'data' => $report,
        ]);
    }

    private function xmlResponse(string $xml, string $filename): Response
    {
        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
