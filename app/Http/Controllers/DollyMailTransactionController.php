<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DollyMailTransactionController extends Controller
{
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

}
