<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Organisation;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Mail;
use App\Models\MailType;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\MailAttachment;


class TransactionController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $transactions = Transaction::where('user_send', $user->id)->get();
        return view('transactions.index', compact('transactions'));
    }



    public function create()
    {
        return view('transactions.create');
    }



    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|integer',
            'date_creation' => 'required|date',
            'mail_id' => 'required|exists:mails,id',
            'user_send' => 'required|exists:users,id',
            'organisation_send_id' => 'required|exists:organisations,id',
            'user_received' => 'nullable|exists:users,id',
            'organisation_received_id' => 'nullable|exists:organisations,id',
            'mail_status_id' => 'required|exists:mail_status,id',
        ]);

        Transaction::create($request->all());

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction created successfully.');
    }



    public function show(Transaction $transaction)
    {
        return view('transactions.show', compact('transaction'));
    }



    public function edit(Transaction $transaction)
    {
        return view('transactions.edit', compact('transaction'));
    }



    public function update(Request $request, Transaction $transaction)
    {
        $request->validate([
            'code' => 'required|integer',
            'date_creation' => 'required|date',
            'mail_id' => 'required|exists:mails,id',
            'user_send' => 'required|exists:users,id',
            'organisation_send_id' => 'required|exists:organisations,id',
            'user_received' => 'nullable|exists:users,id',
            'organisation_received_id' => 'nullable|exists:organisations,id',
            'mail_status_id' => 'required|exists:mail_status,id',
        ]);

        $transaction->update($request->all());

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction updated successfully');
    }



    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully');
    }
}




