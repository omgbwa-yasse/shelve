<?php

namespace App\Http\Controllers;

use App\Models\documentType;
use App\Models\MailAction;
use App\Models\Mail;
use App\Models\User;
use App\Models\MailType;
use App\Models\MailStatus;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\Organisation;
use App\Models\UserOrganisation;
use Illuminate\Http\Request;
use App\Models\MailAttachment;
use App\Models\MailTransaction;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class MailReceivedController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $transactions = MailTransaction::where('user_received_id', $user->id)->get();
        $transactions->load(['mail','action','organisationSend','organisationReceived']);
        return view('mails.received.index', compact('transactions'));
    }



    public function create()
    {
        $type = mailType::where('name','=','received');
        $mails = mail::all();
        $users = User::where('id', '!=', auth()->id())->get();
        $organisations = organisation ::all();
        $documentTypes = documentType :: all();
        $mailActions = MailAction :: all();
        $receivedOrganisations = Organisation::whereIn('id', UserOrganisation::where('user_id', auth()->id())
            ->pluck('organisation_id'))
            ->get();
        return view('mails.received.create', compact('mails','users','organisations','receivedOrganisations','documentTypes','mailActions'));
    }



    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'code' => 'required',
            'mail_id' => 'required|exists:mails,id',
            'user_send_id' => 'required|exists:users,id',
            'organisation_send_id' => 'required|exists:organisations,id',
            'organisation_received_id' => 'required|exists:organisations,id',
            'document_type_id' => 'required|exists:document_types,id',
            'action_id' => 'required|exists:mail_actions,id',
            'description' => 'nullable',
        ]);


        $validatedData['user_received_id'] =  auth()->id();
        $validatedData['date_creation'] = now();
        $validatedData['mail_type_id'] = 1; // 1 Recevoir et 2 Emettre



        MailTransaction::create($validatedData);

        return redirect()->route('mail-received.index')
            ->with('success', 'MailTransaction created successfully.');
    }




    public function show(int $id)
    {
        $mailTransaction = MailTransaction::with([
            'mail',
            'documentType',
            'userReceived',
            'userSend',
            'organisationReceived',
            'organisationSend'
        ])->findOrFail($id);

        return view('mails.received.show', compact('mailTransaction'));
    }



    public function edit(INT $id)
    {
        $mails = mail::all();
        $users = User::where('id', '!=', auth()->id())->get();
        $organisations = organisation ::all();
        $receivedOrganisations = Organisation::whereIn('id', UserOrganisation::where('user_id', auth()->id())
            ->where('active',true)
            ->pluck('organisation_id'))
            ->get();
        $documentTypes = documentType :: all();
        $mailActions = MailAction :: all();
        $mailTransaction = MailTransaction ::findOrFail($id);
        return view('mails.received.edit', compact('mails','users','mailTransaction','receivedOrganisations','organisations','documentTypes','mailActions'));
    }



    public function update(Request $request, INT $id)
    {
        $mailTransaction = MailTransaction ::findOrFail($id);
        $validatedData = $request->validate([
            'code' => 'required',
            'mail_id' => 'required|exists:mails,id',
            'user_send_id' => 'required|exists:users,id',
            'organisation_send_id' => 'required|exists:organisations,id',
            'organisation_received_id' => 'required|exists:organisations,id',
            'document_type_id' => 'required|exists:document_types,id',
            'action_id' => 'required|exists:mail_actions,id',
            'description' => 'nullable',
        ]);

        $validatedData['user_received_id'] =  auth()->id();
        $validatedData['date_creation'] = now();
        $validatedData['mail_type_id'] = 1; // 1 Recevoir et 2 Emettre

        $mailTransaction->update($validatedData);

        return redirect()->route('mail-received.index')
            ->with('success', 'Mail Transaction updated successfully');
    }



    public function destroy($id)
    {
        $mailTransaction = MailTransaction::findOrFail($id);
        $mailTransaction->delete();
        return redirect()->route('mail-received.index')->with('success', 'MailTransaction deleted successfully');
    }
}

