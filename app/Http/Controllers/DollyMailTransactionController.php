<?php

namespace App\Http\Controllers;

use App\Models\DollyMailTransaction;
use App\Models\Dolly;
use App\Models\MailTransaction;
use Illuminate\Http\Request;

class DollyMailTransactionController extends Controller
{


    public function index()
    {
        $dollyMailTransactions = DollyMailTransaction::with(['mailTransaction', 'dolly'])->get();
        return view('dollies.mails.transactions', compact('dollyMailTransactions'));
    }





    public function create()
    {
        $dollies = Dolly::all();
        $mailTransactions = MailTransaction::all();
        return view('dollies.mails.transactions.create', compact('dollies', 'mailTransactions'));
    }





    public function store(Request $request)
    {
        $request->validate([
            'dolly_id' => 'required|exists:dollies,id',
            'mail_transaction_id' => 'required|exists:mail_transactions,id',
        ]);

        DollyMailTransaction::create($request->all());

        return redirect()->route('dolly-mail-transactions.index')
                        ->with('success','Relation créée avec succès.');
    }




    public function edit(DollyMailTransaction $dollyMailTransaction)
    {
        $dollies = Dolly::all();
        $mailTransactions = MailTransaction::all();
        return view('dollies.mails.transactions.edit', compact('dollyMailTransaction', 'dollies', 'mailTransactions'));
    }




    public function update(Request $request, DollyMailTransaction $dollyMailTransaction)
    {
        $request->validate([
            'dolly_id' => 'required|exists:dollies,id',
            'mail_transaction_id' => 'required|exists:mail_transactions,id',
        ]);

        $dollyMailTransaction->update($request->all());

        return redirect()->route('dolly-mail-transactions.index')
                        ->with('success','Relation mise à jour avec succès');
    }


    public function destroy(DollyMailTransaction $dollyMailTransaction)
    {
        $dollyMailTransaction->delete();

        return redirect()->route('dolly-mail-transactions.index')
                        ->with('success','Relation supprimée avec succès');
    }



    public function apiList(Dolly $dolly)
    {
        $mailTransactions = $dolly->mailTransactions;
        return response()->json($mailTransactions);
    }



    public function apiStore(Request $request)
    {
        $request->validate([
            'dolly_id' => 'required|exists:dollies,id',
            'mail_transaction_id' => 'required|exists:mail_transactions,id',
        ]);

        // Vérifier si la relation existe déjà
        $exists = DollyMailTransaction::where('dolly_id', $request->dolly_id)
            ->where('mail_transaction_id', $request->mail_transaction_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Ce document est déjà dans le chariot'
            ]);
        }

        $dollyMailTransaction = DollyMailTransaction::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Document ajouté au chariot avec succès',
            'data' => $dollyMailTransaction
        ]);
    }


    public function apiDestroy($dollyId, $mailTransactionId)
    {
        $dollyMailTransaction = DollyMailTransaction::where('dolly_id', $dollyId)
            ->where('mail_transaction_id', $mailTransactionId)
            ->first();

        if (!$dollyMailTransaction) {
            return response()->json([
                'success' => false,
                'message' => 'Relation non trouvée'
            ], 404);
        }

        $dollyMailTransaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document retiré du chariot avec succès'
        ]);
    }


    public function apiEmptyDolly(Dolly $dolly)
    {
        DollyMailTransaction::where('dolly_id', $dolly->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Chariot vidé avec succès'
        ]);
    }


    public function process(Dolly $dolly)
    {
        $mailTransactions = $dolly->mailTransactions;

        return view('dollies.mails.transactions.process', compact('dolly', 'mailTransactions'));
    }

}
