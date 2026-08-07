<?php

namespace App\Http\Controllers;

use App\Exports\SEDAExport;
use App\Models\Record;
use App\Models\Slip;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SedaZipBuilder;

class SEDAExportController extends Controller
{
    public function exportSlip(Request $request, Slip $slip): Response
    {
        $slip->load(['records.attachments', 'records.containers', 'records.level', 'records.parent', 'records.thesaurusConcepts']);

        if ($request->boolean('zip')) {
            /** @var SedaZipBuilder $zipper */
            $zipper = app(SedaZipBuilder::class);
            [$path, $filename] = $zipper->buildForSlip($slip);
            return response()->download($path, $filename)->deleteFileAfterSend(true);
        }

        $exporter = app(SEDAExport::class);
        $xml = $exporter->export(collect([$slip]));
        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="seda_slip_' . $slip->id . '.xml"',
        ]);
    }

    public function exportRecord(Request $request, Record $record): Response
    {
        $record->load(['attachments', 'containers', 'level', 'parent', 'thesaurusConcepts']);

        if ($request->boolean('zip')) {
            /** @var SedaZipBuilder $zipper */
            $zipper = app(SedaZipBuilder::class);
            [$path, $filename] = $zipper->buildForRecord($record);
            return response()->download($path, $filename)->deleteFileAfterSend(true);
        }

        $exporter = app(SEDAExport::class);
        $xml = $exporter->exportRecords(collect([$record]));
        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="seda_record_' . $record->id . '.xml"',
        ]);
    }
}
