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

class MailSendController extends Controller
{

    public function index()
    {
        $id = Auth::user()->current_organisation_id;
        $transactions = MailTransaction::where('organisation_send_id', $id)->get();
        $transactions->load(['mail','action','organisationReceived','organisationSend']);
        return view('mails.send.index', compact('transactions'));
    }


// Pour les courriers envoyés (MailSend)
    public function create()
    {
        $currentOrganisationId = Auth::user()->current_organisation_id;

        $mails = Mail::with(['transactions', 'type'])
            ->where(function ($query) use ($currentOrganisationId) {
                $query->whereHas('transactions', function ($subquery) use ($currentOrganisationId) {
                    $subquery->where('organisation_send_id', $currentOrganisationId);
                })
                ->orWhere('creator_organisation_id', $currentOrganisationId);
            })
            ->latest()
            ->get();


        $users = User::where('id', '!=', Auth::id())
            ->orderBy('name')
            ->get();


        $organisations = Organisation::whereNot('id', $currentOrganisationId)
            ->orderBy('name')
            ->get();

        $documentTypes = DocumentType::orderBy('name')->get();
        $mailActions = MailAction::orderBy('name')->get();


        $sendOrganisations = Organisation::whereNot('id', $currentOrganisationId)
            ->orderBy('name')
            ->get();

        return view('mails.send.create', compact(
            'mails',
            'users',
            'organisations',
            'sendOrganisations',
            'documentTypes',
            'mailActions'
        ));
    }

    public function store(Request $request)
    {
        $code = $this->setMailTransactionCode();

        $request->merge(['code' => $code]);

        $validatedData = $request->validate([
            'code' => 'required',
            'mail_id' => 'required|exists:mails,id',
            'user_received_id' => 'required|exists:users,id',
            'organisation_received_id' => 'required|exists:organisations,id',
            'document_type_id' => 'required|exists:document_types,id',
            'action_id' => 'required|exists:mail_actions,id',
            'description' => 'nullable',
        ]);

        $validatedData['organisation_send_id'] = auth()->user()->current_organisation_id;
        $validatedData['user_send_id'] =  auth()->id();

        $validatedData['date_creation'] = now();
        $validatedData['mail_type_id'] = 3; // 1 Recevoir, 2 Emettre 3 en cours (in progress)



        MailTransaction::create($validatedData);

        return redirect()->route('mail-send.index')
            ->with('success', 'MailTransaction created successfully.');
    }



    public function setMailTransactionCode(){
        $year = now()->format('Y');

        $lastTransaction = MailTransaction::whereYear('created_at', $year)
                                             ->latest('id')
                                             ->first();

        if ($lastTransaction) {
            $lastcode = explode('-', $lastTransaction->code);
            $lastOrderNumber = isset($lastcode[1]) ? (int)substr($lastcode[1], 1) : 0;
            $transactionNumber = $lastOrderNumber + 1;
        } else {
            $transactionNumber = 1;
        }

        $newNumberFormatted = str_pad($transactionNumber, 7, '0', STR_PAD_LEFT);
        return 'T' . $year . "-" . $newNumberFormatted;
    }




    public function show(int $id)
    {
        $mailTransaction = MailTransaction::with([
            'mail',
            'mail.attachments',
            'documentType',
            'userReceived',
            'userSend',
            'organisationReceived',
            'organisationSend',
            'action',
            'mailType'
        ])->findOrFail($id);

        // Récupérer l'historique des transactions pour ce mail
        $mailHistory = MailTransaction::where('mail_id', $mailTransaction->mail_id)
            ->with(['userSend', 'userReceived', 'organisationSend', 'organisationReceived', 'action'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('mails.send.show', compact('mailTransaction', 'mailHistory'));
    }



    public function edit(INT $id)
    {
        $mails = mail::all();
        $users = User::where('id', '!=', auth()->id())->get();

        $organisations = Organisation::whereNot('id', auth()->user()->currentOrganisation->id)->get();

        $sendOrganisations = Organisation::whereIn('id', UserOrganisation::where('user_id', auth()->id())
            ->where('active',true)
            ->pluck('organisation_id'))
            ->get();
        $documentTypes = documentType :: all();
        $mailActions = MailAction :: all();
        $mailTransaction = MailTransaction ::findOrFail($id);
        return view('mails.send.edit', compact('mails','users','mailTransaction','sendOrganisations','organisations','documentTypes','mailActions'));
    }



    public function update(Request $request, INT $id)
    {
        $mailTransaction = MailTransaction ::findOrFail($id);
        $validatedData = $request->validate([
            'code' => 'required',
            'mail_id' => 'required|exists:mails,id',
            'user_received_id' => 'required|exists:users,id',
            'organisation_received_id' => 'required|exists:organisations,id',
            'document_type_id' => 'required|exists:document_types,id',
            'action_id' => 'required|exists:mail_actions,id',
            'description' => 'nullable',
        ]);

        $validatedData['organisation_send_id'] = auth()->user()->organisation->id ;
        $validatedData['user_send_id'] =  auth()->id();

        $validatedData['date_creation'] = now();
        $validatedData['mail_type_id'] = 3; // 1 Recevoir, 2 Emettre 3 en cours (in progress)

        $mailTransaction->update($validatedData);

        return redirect()->route('mail-send.index')
            ->with('success', 'Mail Transaction updated successfully');
    }

    public function destroy($id)
    {
        $mailTransaction = MailTransaction::findOrFail($id);
        $mailTransaction->delete();
        return redirect()->route('mail-send.index')->with('success', 'MailTransaction deleted successfully');
    }
}

