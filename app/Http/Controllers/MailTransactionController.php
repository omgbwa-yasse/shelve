<?php

namespace App\Http\Controllers;

use App\Exports\MailsExport; // Adapter l'export aux mails
use App\Models\Mail;
use App\Models\Dolly;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Auth;


class MailTransactionController extends Controller
{
    public function export(Request $request)
    {
        $selectedIds = $request->validate([
            'selectedIds' => 'required|array',
            'selectedIds.*' => 'integer|exists:mails,id'
        ])['selectedIds'];

        $mails = Mail::whereIn('id', $selectedIds)
                     ->with(['action', 'sender', 'recipient', 'senderOrganisation', 'recipientOrganisation']) // Relations adaptées
                     ->get();

        $export = new MailsExport($mails);
        return Excel::download($export, 'courriers-' . date('Y-m-d') . '.xlsx');
    }

    public function print(Request $request)
    {
        $selectedIds = $request->validate([
            'selectedIds' => 'required|array',
            'selectedIds.*' => 'integer|exists:mails,id' // Valider sur la table mails
        ])['selectedIds'];

        $mails = Mail::whereIn('id', $selectedIds)
                     ->with(['action', 'sender', 'recipient', 'senderOrganisation', 'recipientOrganisation']) // Relations adaptées
                     ->get();

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isPhpEnabled', true);

        $dompdf = new Dompdf($pdfOptions);
        $html = $this->generatePdfHtml($mails); // Passer les mails à la vue

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $dompdf->stream('courriers-' . date('Y-m-d') . '.pdf');
    }

    private function generatePdfHtml($mails) // Accepter les mails en paramètre
    {
        return view('mails.print', [ // Adapter le nom de la vue
            'mails' => $mails, // Passer les mails à la vue
            'generatedAt' => now()->format('d/m/Y H:i'),
            'totalCount' => $mails->count(),
        ])->render();
    }

    public function createDolly(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'type_id' => 'required|exists:dolly_types,id',
        ]);

        $dolly = Dolly::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'type_id' => $request->input('type_id'),
            'user_id' => Auth::id(),
        ]);

        return response()->json(['dolly_id' => $dolly->id]);
    }

    public function addRecordToDolly(Request $request)
    {
        $request->validate([
            'dolly_id' => 'required|integer|exists:dollies,id',
            'selectedIds' => 'required|array',
            'selectedIds.*' => 'integer|exists:mails,id' // Valider sur la table mails
        ]);

        $dollyId = $request->input('dolly_id');
        $selectedIds = $request->input('selectedIds');

        $dolly = Dolly::findOrFail($dollyId);
        $dolly->mails()->sync($selectedIds); // Assumer une relation "mails" dans le modèle Dolly

        return response()->json(['message' => 'Mails ajoutés au dolly avec succès.']);
    }
}
