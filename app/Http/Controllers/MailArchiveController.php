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
        $mailArchives = MailArchive::with(['container','mail'])
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

        $mails = Mail::where('recipient_user_id', Auth::id())
                    ->where('is_archived', false)
                    ->where('recipient_organisation_id', Auth::user()->current_organisation_id)
                    ->where('status', '!=', 'transmitted') 
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
            // Créer l'enregistrement d'archivage
            MailArchive::create([
                'container_id' => $request->container_id,
                'mail_id' => $mail['id'],
                'archived_by' => Auth::id(),
                'document_type' => $mail['document_type'],
            ]);
            
            // Mettre à jour le statut is_archived du mail
            Mail::where('id', $mail['id'])->update([
                'is_archived' => true,
                'updated_at' => now(), // Enregistre la mise à jour avec un horodatage
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




    public function getAvailableMailsForArchive($containerId)
    {
        $container = MailContainer::findOrFail($containerId);
        
        // Récupérer les mails non archivés de l'utilisateur courant
        $mails = Mail::where('recipient_user_id', Auth::id())
                    ->where('is_archived', false)
                    ->where('recipient_organisation_id', Auth::user()->current_organisation_id)
                    ->where('status', '!=', 'transmitted')
                    ->get(['id', 'subject', 'sender_name', 'received_date']);
        
        return response()->json([
            'success' => true,
            'container' => [
                'id' => $container->id,
                'name' => $container->name
            ],
            'mails' => $mails
        ]);
    }

    
    

    

    
    

    public function getArchivedMails($containerId)
    {
        $container = MailContainer::findOrFail($containerId);
        $archivedMails = MailArchive::with(['mail:id,subject,sender_name,received_date'])
                                ->where('container_id', $containerId)
                                ->get(['id', 'mail_id', 'document_type', 'created_at']);
        
        return response()->json([
            'success' => true,
            'container' => [
                'id' => $container->id,
                'name' => $container->name
            ],
            'archived_mails' => $archivedMails
        ]);
    }

    
    

    public function removeMails(Request $request, int $containerId)
    {
        $request->validate([
            'archive_mail_id' => 'required|exists:mail_archives,id',
        ]);
        
        $archive = MailArchive::findOrFail($request->archive_mail_id);
        
        if ($archive->container_id != $containerId) {
            return response()->json([
                'success' => false,
                'message' => 'Cet archivage n\'appartient pas au conteneur spécifié'
            ], 403);
        }
        
        $archive->delete();
        
        return response()->json([
            'success' => true,
            'container_id' => $containerId,
        ], 200);
    }



    public function addMails(Request $request, $containerId)
    {
        $request->validate([
            'mails' => 'required|array',
            'mails.*.id' => 'required|exists:mails,id',
            'mails.*.document_type' => 'required|in:original,duplicate,copy',
        ]);
        

        foreach ($request->mails as $mail) {
            MailArchive::create([
                'container_id' => $containerId,
                'mail_id' => $mail['id'],
                'archived_by' => Auth::user()->id,
                'document_type' => $mail['document_type'],
            ]);
        }

        
        return response()->json([
            'success' => true,
        ], 200);
    }


}
