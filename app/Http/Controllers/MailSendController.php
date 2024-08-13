<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Organisation;
use App\Models\MailTransaction;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Mail;
use App\Models\MailType;
use App\Models\MailStatus;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\MailAttachment;
use App\Models\documentType;
use App\Models\MailAction;

class MailSendController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $transactions = MailTransaction::where('user_send_id', $user->id)->get();
        $transactions->load(['mails','action','organisationSend','organisationReceived']);
        return view('mails.send.index', compact('transactions'));
    }


    public function create()
    {
        $type = mailType::where('name','=','send');
        $mails = mail::all();
        $users = User::where('id', '!=', auth()->id())->get();
        $organisations = organisation ::all();
        $mailStatuses = MailStatus:: all();
        $documentTypes = documentType :: all();
        $mailActions = MailAction :: all();
        return view('mails.send.create', compact('mails','users','organisations','mailStatuses','documentTypes','mailActions'));
    }




    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|integer',
            'date_creation' => 'required|date',
            'mail_id' => 'required|exists:mails,id',
            'user_send_id' => 'required|exists:users,id',
            'organisation_send_id' => 'required|exists:organisations,id',
            'user_received_id' => 'nullable|exists:users,id',
            'organisation_received_id' => 'nullable|exists:organisations,id',
            'mail_status_id' => 'required|exists:mail_statuses,id',
            'action_id' => 'required|exists:mail_actions,id',
            'description' => 'nullable',
        ]);

        MailTransaction::create($request->all());

        return redirect()->route('mail-send.index')
            ->with('success', 'MailTransaction created successfully.');
    }



    public function show(INT $id)
    {
        $transaction = MailTransaction::with([
            'mails',
            'action',
            'documentType',
            'mailStatus',
            'userReceived',
            'userSend',
            'organisationReceived',
            'organisationSend'
        ])->findOrFail($id);
        return view('mails.send.show', compact('transaction'));
    }



    public function edit(MailTransaction $MailTransaction)
    {
        return view('mails.send.edit', compact('MailTransaction'));
    }



    public function update(Request $request, MailTransaction $MailTransaction)
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
            'action_id' => 'required|exists:mail_actions,id',
            'description' => 'nullable',
        ]);

        $MailTransaction->update($request->all());

        return redirect()->route('mails.send.index')
            ->with('success', 'MailTransaction updated successfully');
    }



    public function destroy(MailTransaction $MailTransaction)
    {
        $MailTransaction->delete();

        return redirect()->route('mails.send.index')
            ->with('success', 'MailTransaction deleted successfully');
    }
}




