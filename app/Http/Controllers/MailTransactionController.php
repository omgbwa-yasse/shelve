<?php

namespace App\Http\Controllers;

use App\Exports\MailTransactionExport;
use App\Models\documentType;
use App\Models\MailAction;
use App\Models\Mail;
use App\Models\Dolly;
use App\Models\User;
use App\Models\MailType;
use App\Models\MailStatus;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\Organisation;
use App\Models\UserOrganisation;
use App\Models\MailAttachment;
use App\Models\MailTransaction;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Dompdf\Dompdf;
use Dompdf\Options;

class MailTransactionController extends Controller
{





    public function export(Request $request)
    {
        $selectedIds = $request->validate([
            'selectedIds' => 'required|array',
            'selectedIds.*' => 'integer|exists:mail_transactions,id'
        ])['selectedIds'];

        $transactions = MailTransaction::whereIn('id', $selectedIds)
            ->with(['mail', 'action', 'userSend', 'userReceived', 'organisationSend', 'organisationReceived', 'documentType'])
            ->get();

        $export = new MailTransactionExport($transactions);
        return Excel::download($export, 'courriers-' . date('Y-m-d') . '.xlsx');
    }




    public function print(Request $request)
    {
        $selectedIds = $request->validate([
            'selectedIds' => 'required|array',
            'selectedIds.*' => 'integer|exists:mail_transactions,id'
        ])['selectedIds'];

        $transactions = MailTransaction::whereIn('id', $selectedIds)
            ->with(['mail', 'action', 'userSend', 'userReceived', 'organisationSend', 'organisationReceived', 'documentType'])
            ->get();

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isPhpEnabled', true);

        $dompdf = new Dompdf($pdfOptions);
        $html = $this->generatePdfHtml($transactions);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $dompdf->stream('courriers-' . date('Y-m-d') . '.pdf');
    }





    private function generatePdfHtml($transactions)
    {
        return view('mails.transactions.print', [
            'transactions' => $transactions,
            'generatedAt' => now()->format('d/m/Y H:i'),
            'totalCount' => $transactions->count(),
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
            'selectedIds.*' => 'integer|exists:mail_transactions,id'
        ]);

        $dollyId = $request->input('dolly_id');
        $selectedIds = $request->input('selectedIds');


        $dolly = Dolly::findOrFail($dollyId);
        $dolly->mailTransactions()->sync($selectedIds);

        return response()->json(['message' => 'Transactions ajoutées au dolly avec succès.']);
    }


}
