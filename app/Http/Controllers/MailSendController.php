<?php

namespace App\Http\Controllers;

use App\Models\Mail;
use App\Models\Dolly;
use App\Models\MailAttachment;
use App\Models\user;
use App\Models\DollyType;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\MailAction;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MailSendController extends Controller
{
    public function index()
    {
        $organisationId = Auth::user()->current_organisation_id;
        $mails = Mail::with(['action', 'recipient', 'recipientOrganisation'])
                     ->where('sender_organisation_id', $organisationId)
                     ->where('status', '!=', 'draft')
                     ->get();
        $dollies = Dolly::all();
        $types = DollyType::all();
        $users = User::all();
        return view('mails.send.index', compact('mails', 'dollies', 'types', 'users'));
    }



    public function create()
    {
        $currentOrganisationId = Auth::user()->current_organisation_id;

        $mailActions = MailAction::orderBy('name')->get();
        $recipientOrganisations = Organisation::where('id', '!=', $currentOrganisationId)->orderBy('name')->get();
        $users = User::all();
        $priorities = MailPriority::all();
        $typologies = MailTypology::all();
        return view('mails.send.create', compact('mailActions', 'recipientOrganisations','users', 'priorities','typologies'));
    }


    public function store(Request $request)
    {
        $mailCode = $this->generateMailCode();

        $validatedData = $request->validate([
            'name' => 'required|max:150',
            'date' => 'required|date',
            'description' => 'nullable',
            'document_type' => 'required|in:original,duplicate,copy',
            'action_id' => 'required|exists:mail_actions,id',
            'recipient_user_id' => 'nullable',
            'recipient_organisation_id' => 'required|exists:organisations,id',
            'priority_id' => 'required|exists:mail_priorities,id',
            'typology_id' => 'required|exists:mail_typologies,id',
            'attachments.*' => 'file|max:10240', // Validation des fichiers (max 10MB chacun)
        ]);

        // Créer le mail
        $mail = Mail::create($validatedData + [
                'code' => $mailCode,
                'sender_organisation_id' => auth()->user()->current_organisation_id,
                'sender_user_id' => auth()->id(),
                'status' => 'in_progress',
            ]);

        // Traiter les pièces jointes
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Générer un nom unique pour le fichier
                $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();

                // Stocker le fichier
                $path = $file->storeAs('mail_attachments', $fileName, 'public');

                // Créer l'enregistrement de la pièce jointe
                $attachment = MailAttachment::create([
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'creator_id' => auth()->id(),
                ]);

                // Lier la pièce jointe au mail
                $mail->attachments()->attach($attachment->id, [
                    'added_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        return redirect()->route('mail-send.index')
            ->with('success', 'Mail créé avec succès avec les pièces jointes.');
    }

    public function update(Request $request, int $id)
    {
        $mail = Mail::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|max:150',
            'date' => 'required|date',
            'description' => 'nullable',
            'document_type' => 'required|in:original,duplicate,copy',
            'action_id' => 'required|exists:mail_actions,id',
            'recipient_user_id' => 'exists:users,id',
            'recipient_organisation_id' => 'required|exists:organisations,id',
            'priority_id' => 'required|exists:mail_priorities,id',
            'typology_id' => 'required|exists:mail_typologies,id',
            'attachments.*' => 'file|max:10240', // Validation des fichiers (max 10MB chacun)
        ]);

        $mail->update($validatedData);

        // Traiter les nouvelles pièces jointes
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('mail_attachments', $fileName, 'public');

                $attachment = MailAttachment::create([
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'creator_id' => auth()->id(),
                ]);

                $mail->attachments()->attach($attachment->id, [
                    'added_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        return redirect()->route('mail-send.index')
            ->with('success', 'Mail mis à jour avec succès');
    }

    // Méthode pour supprimer une pièce jointe spécifique
    public function removeAttachment($mailId, $attachmentId)
    {
        $mail = Mail::findOrFail($mailId);
        $attachment = MailAttachment::findOrFail($attachmentId);

        // Vérifier les permissions
        if ($mail->sender_user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Supprimer le fichier physique
        if (Storage::disk('public')->exists($attachment->path)) {
            Storage::disk('public')->delete($attachment->path);
        }

        // Détacher la relation
        $mail->attachments()->detach($attachmentId);

        // Supprimer l'enregistrement de la pièce jointe
        $attachment->delete();

        return response()->json(['success' => true]);
    }


    public function generateMailCode()
    {
        $year = date('Y');
        $lastMailCode = Mail::whereYear('created_at', $year)
                            ->latest('created_at')
                            ->value('code');

        if ($lastMailCode) {
            $lastCodeParts = explode('-', $lastMailCode);
            $lastOrderNumber = isset($lastCodeParts[1]) ? (int) substr($lastCodeParts[1], 1) : 0;
            $mailCount = $lastOrderNumber + 1;
        } else {
            $mailCount = 1;
        }

        $formattedMailCount = str_pad($mailCount, 6, '0', STR_PAD_LEFT);
        return 'M' . $year . '-' . $formattedMailCount;
    }

    public function show(int $id)
    {
        $mail = Mail::with([
                            'action',
                            'sender',
                            'senderOrganisation',
                            'recipient',
                            'recipientOrganisation',
                            'authors',
                            'attachments'
                        ])
                    ->findOrFail($id);

        return view('mails.send.show', compact('mail'));
    }

    public function edit(int $id)
    {
        $mail = Mail::with([
                            'action',
                            'recipient',
                            'recipientOrganisation',
                            'priority',
                            'typology',
                            'authors',
                            'attachments'
                        ])
                    ->findOrFail($id);

        $mailActions = MailAction::all();
        $recipientOrganisations = Organisation::whereNot('id', auth()->user()->current_organisation_id)->get();

        return view('mails.send.edit', compact('mail', 'mailActions', 'recipientOrganisations'));
    }


    public function destroy($id)
    {
        $mail = Mail::findOrFail($id);
        $mail->delete();

        return redirect()->route('mail-send.index')->with('success', 'Mail deleted successfully');
    }
}
