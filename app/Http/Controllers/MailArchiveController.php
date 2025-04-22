<?php

namespace App\Http\Controllers;

use App\Models\MailArchive; // Nom du modèle corrigé
use Illuminate\Http\Request;
use App\Models\MailContainer;
use App\Models\Mail;
use App\Models\Dolly;
use App\Models\DollyType;
use Illuminate\Support\Facades\Auth;

class MailArchiveController extends Controller // Nom du contrôleur corrigé
{
    public function index($id)
    {
        $mailArchives = MailArchive::with('container', 'mail', 'user') // Relations corrigées
                                  ->where('container_id', '=', $id)
                                  ->get(); // Ajouté pour récupérer la collection

        return view('mails.archives.index', compact('mailArchives')); // Nom de la vue corrigé
    }



    public function archived()
    {
        $mailArchives = Mail::with(['priority', 'authors', 'typology', 'sender', 'recipient'])
                     ->where('is_archived', true)
                     ->paginate(15);
        return view('mails.archives.index', compact('mailArchives'));
    }




    public function show(int $id)
    {
        $mailArchive = MailArchive::with('container', 'mail', 'user')->findOrFail($id); // Relations corrigées

        return view('mails.archives.show', compact('mailArchive')); // Nom de la vue corrigé
    }



    public function create()
    {
        $mailContainers = MailContainer::where('creator_organisation_id', auth::user()->current_organisation_id)
                                      ->get();

        $mails = Mail::where('sender_user_id', Auth::id()) // Champ corrigé
                    ->where('is_archived', false) // Condition ajoutée d'après la logique métier
                    ->where('sender_organisation_id', Auth::user()->current_organisation_id) // Champ corrigé
                    ->get();


        return view('mails.archives.create', compact('mailContainers', 'mails')); // Nom de la vue corrigé
    }




    public function store(Request $request)
    {
        $request->validate([
            'container_id' => 'required|exists:mail_containers,id',
            'mails' => 'required|array',
            'mails.*.id' => 'required|exists:mails,id',
            'mails.*.document_type' => 'required|in:original,duplicate,copy',
        ]);

        $mailsArchived = 0;
        
        foreach ($request->mails as $mail) {
            MailArchive::create([
                'container_id' => $request->container_id,
                'mail_id' => $mail['id'],
                'archived_by' => Auth::id(),
                'document_type' => $mail['document_type'],
            ]);
            
            $mailsArchived++;
        }

        $message = $mailsArchived === 1 
            ? 'Un mail a été archivé avec succès.' 
            : $mailsArchived . ' mails ont été archivés avec succès.';
        
        return redirect()->route('mail-container.index')->with('success', $message);
    }





    public function edit(MailArchive $mailArchive) // Nom de la variable corrigé
    {
        $mailContainers = MailContainer::where('creator_organisation_id', auth::user()->current_organisation_id)
                                      ->get();

        $mails = Mail::where('sender_user_id', Auth::id())
                    ->where('sender_organisation_id', Auth::user()->current_organisation_id)
                    ->get();

        return view('mails.archives.edit', compact('mailArchive', 'mailContainers', 'mails')); // Nom de la vue corrigé
    }



    public function update(Request $request, MailArchive $mailArchive) // Nom de la variable corrigé
    {
        $request->validate([
            'container_id' => 'required|exists:mail_containers,id',
            'mail_id' => 'required|exists:mails,id',
            'document_type' => 'required|in:original,duplicate,copy',
        ]);

        $mailArchive->update([
            'container_id' => $request->container_id,
            'mail_id' => $request->mail_id,
            'document_type' => $request->document_type,
        ]);

        return redirect()->route('mail-archive.index')->with('success', 'Mail archive updated successfully.'); // Route corrigée
    }



    public function destroy(MailArchive $mailArchive) // Nom de la variable corrigé
    {
        $mailArchive->delete();

        return redirect()->route('mail-archive.index')->with('success', 'Mail archive deleted successfully.'); // Route corrigée
    }
}
