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

    public function inprogress()
    {
        $user = Auth::user();
        $transactions = MailTransaction::where('user_received_id', $user->id)
            ->where('mail_type_id', 3)
            ->get();
        $transactions->load(['mail','action','organisationSend','organisationReceived']);
        return view('mails.received.index', compact('transactions'));
    }



    public function approve(Request $request)
    {

        $validatedData = $request->validate([
            'id' => 'required|exists:mail_transactions,id',
        ]);

        $mailTransaction = MailTransaction::findOrFail($validatedData['id']);

        $updateData = [
            'user_received_id' => auth()->id(),
            'date_creation' => $mailTransaction->created_at,
            'mail_type_id' => 1, // 1 Recevoir
        ];

        $mailTransaction->update($updateData);
        return redirect()->route('mail-received.index')
            ->with('success', 'Mail Transaction updated successfully');
    }





    public function create()
    {
        // Get the current user's organisation ID
        $currentOrganisationId = Auth::user()->current_organisation_id;

        // Get mails that are related to the current organisation through transactions
        $mails = Mail::with(['transactions', 'type'])
            ->whereHas('transactions', function ($query) use ($currentOrganisationId) {
                $query->where('organisation_received_id', $currentOrganisationId);
            })
            ->latest()
            ->get();

        // Get mail type for received mails
        $mailType = MailType::where('name', 'received')->first();

        // Get all users except the current user
        $users = User::where('id', '!=', Auth::id())
            ->orderBy('name')
            ->get();

        // Get all organisations except the current user's organisation
        $organisations = Organisation::whereNot('id', $currentOrganisationId)
            ->orderBy('name')
            ->get();

        // Get all document types
        $documentTypes = DocumentType::orderBy('name')->get();

        // Get all mail actions
        $mailActions = MailAction::orderBy('name')->get();

        // Get received organisations for the current user
        $receivedOrganisations = Auth::user()->organisations;

        return view('mails.received.create', compact(
            'mails',
            'mailType',
            'users',
            'organisations',
            'receivedOrganisations',
            'documentTypes',
            'mailActions'
        ));
    }


    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'code' => 'required',
            'mail_id' => 'required|exists:mails,id',
            'user_send_id' => 'required|exists:users,id',
            'organisation_send_id' => 'required|exists:organisations,id',

            'document_type_id' => 'required|exists:document_types,id',
            'action_id' => 'required|exists:mail_actions,id',
            'description' => 'nullable',
        ]);

        $validatedData['organisation_received_id'] = auth()->user()->current_organisation_id ;
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
        $organisations = Organisation::whereNot('id', auth()->user()->currentOrganisation->id)->get();
        $receivedOrganisations = auth()->user()->organisation;
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
            'document_type_id' => 'required|exists:document_types,id',
            'action_id' => 'required|exists:mail_actions,id',
            'description' => 'nullable',
        ]);

        $validatedData['organisation_received_id'] = auth()->user()->organisation->id ;
        $validatedData['user_received_id'] =  auth()->id();

        $validatedData['date_creation'] = now();
        $validatedData['mail_type_id'] = 1; // 1 Recevoir,  2 Emettre et 3 inprogress

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

