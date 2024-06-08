<?php

namespace App\Http\Controllers;
use App\Models\MailTransaction;
use Illuminate\Http\Request;

class MailSendController extends Controller
{
    public function index()
    {
        $transactions = MailTransaction::all();
        return view('mails.send.index', compact('transactions'));
    }

    public function create()
    {
        return view('mails.send.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|integer',
            'date_creation' => 'required|date_format:Y-m-d H:i:s',
            'mail_id' => 'required|integer',
            'user_send' => 'required|integer',
            'organisation_send_id' => 'required|integer',
            'user_received' => 'nullable|integer',
            'organisation_received_id' => 'nullable|integer',
            'mail_status_id' => 'required|integer',
        ]);

        MailTransaction::create($request->all());
        return redirect()->route('transactions.index')->with('success', 'MailTransaction créée avec succès !');
    }

    public function show(MailTransaction $MailTransaction)
    {
        return view('mails.send.show', compact('MailTransaction'));
    }

    public function edit(MailTransaction $MailTransaction)
    {
        return view('mails.send.edit', compact('MailTransaction'));
    }

    public function update(Request $request, MailTransaction $MailTransaction)
    {
        $request->validate([
            'code' => 'required|integer',
            'date_creation' => 'required|date_format:Y-m-d H:i:s',
            'mail_id' => 'required|integer',
            'user_send' => 'required|integer',
            'organisation_send_id' => 'required|integer',
            'user_received' => 'nullable|integer',
            'organisation_received_id' => 'nullable|integer',
            'mail_status_id' => 'required|integer',
        ]);

        $MailTransaction->update($request->all());
        return redirect()->route('transactions.index')->with('success', 'MailTransaction mise à jour avec succès !');
    }

    public function destroy(MailTransaction $MailTransaction)
    {
        $MailTransaction->delete();
        return redirect()->route('transactions.index')->with('success', 'MailTransaction supprimée avec succès !');
    }
}
