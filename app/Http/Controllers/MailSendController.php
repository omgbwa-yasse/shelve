<?php

namespace App\Http\Controllers;

use App\Models\Mail;
use App\Models\Dolly;
use App\Models\User;
use App\Models\DollyType;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\MailAction;
use App\Models\Organisation;
use App\Models\MailAttachment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Image;
use FFMpeg\FFMpeg;
use Illuminate\Support\Facades\File;

class MailSendController extends Controller
{
    protected $allowedMimeTypes = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
        'video/mp4',
        'video/quicktime',
        'video/x-msvideo',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];

    public function index()
    {
        $organisationId = Auth::user()->current_organisation_id;
        $mails = Mail::with(['action', 'recipient', 'recipientOrganisation', 'attachments'])
            ->where('sender_organisation_id', $organisationId)
            ->where('status', '!=', 'draft')
            ->orderBy('created_at', 'desc')
            ->OrWhereHas('containers', function($q) {
                $q->where('creator_organisation_id', Auth::user()->current_organisation_id);
            })
            ->get();


        $dollies = Dolly::all();
        $categories = Dolly::categories();
        $users = User::all();
        return view('mails.send.index', compact('mails', 'dollies', 'categories', 'users'));
    }



    public function create()
    {
        $currentOrganisationId = Auth::user()->current_organisation_id;
        $mailActions = MailAction::orderBy('name')->get();
        $recipientOrganisations = Organisation::where('id', '!=', $currentOrganisationId)
            ->orderBy('name')
            ->get();
        $users = User::orderBy('name')->get();
        $priorities = MailPriority::orderBy('name')->get();
        $typologies = MailTypology::orderBy('name')->get();

        return view('mails.send.create', compact(
            'mailActions',
            'recipientOrganisations',
            'users',
            'priorities',
            'typologies'
        ));
    }


    protected function handleFileUpload($file, $mail)
    {
        try {
            if (!in_array($file->getMimeType(), $this->allowedMimeTypes)) {
                throw new Exception('Type de fichier non autorisé');
            }

            $path = $file->store('mail_attachments');

            $mimeType = $file->getMimeType();
            $fileType = explode('/', $mimeType)[0];

            $attachment = MailAttachment::create([
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'crypt' => md5_file($file),
                'crypt_sha512' => hash_file('sha512', $file->getRealPath()),
                'size' => $file->getSize(),
                'creator_id' => auth()->id(),
                'type' => 'mail',
                'mime_type' => $mimeType,
            ]);

            // Générer la miniature pour les images et vidéos
            if (in_array($fileType, ['image', 'video'])) {
                $thumbnailPath = $this->generateThumbnail($file, $attachment->id, $fileType);
                if ($thumbnailPath) {
                    $attachment->update(['thumbnail_path' => $thumbnailPath]);
                }
            }

            $mail->attachments()->attach($attachment->id, [
                'added_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Erreur lors du téléchargement du fichier : ' . $e->getMessage());
            throw $e;
        }
    }


    protected function generateThumbnail($file, $attachmentId, $fileType)
    {
        $thumbnailPath = 'thumbnails_mail/' . $attachmentId . '.jpg';

        try {
            if ($fileType === 'image') {
                $img = Image::make($file->getRealPath());
                $img->fit(300, 300);
                $img->save(storage_path('app/public/' . $thumbnailPath));
            } elseif ($fileType === 'video') {
                $ffmpeg = FFMpeg::create();
                $video = $ffmpeg->open($file->getRealPath());
                $frame = $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(1));
                $frame->save(storage_path('app/public/' . $thumbnailPath));
            } else {
                return null;
            }

            return $thumbnailPath;
        } catch (Exception $e) {
            Log::error('Erreur lors de la génération de la miniature : ' . $e->getMessage());
            return null;
        }
    }

    public function download($mailId, $attachmentId)
    {
        try {
            $mail = Mail::findOrFail($mailId);
            $attachment = MailAttachment::findOrFail($attachmentId);

            if (!$this->canAccessAttachment($mail)) {
                return abort(403, 'Non autorisé');
            }

            $filePath = storage_path('app/' . $attachment->path);

            if (file_exists($filePath)) {
                $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
                $fileName = $attachment->name . '.' . $fileExtension;
                return response()->download($filePath, $fileName);
            }

            return abort(404);

        } catch (Exception $e) {
            Log::error('Erreur lors du téléchargement du fichier : ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du téléchargement du fichier');
        }
    }

    public function preview($mailId, $attachmentId)
    {
        try {
            $mail = Mail::findOrFail($mailId);
            $attachment = MailAttachment::findOrFail($attachmentId);

            if (!$this->canAccessAttachment($mail)) {
                return abort(403, 'Non autorisé');
            }

            $filePath = storage_path('app/' . $attachment->path);

            if (file_exists($filePath)) {
                return response()->file($filePath);
            }

            return abort(404);

        } catch (Exception $e) {
            Log::error('Erreur lors de la prévisualisation du fichier : ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la prévisualisation du fichier');
        }
    }






    public function OutgoingMail(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|max:150',
                'date' => 'required|date',
                'description' => 'nullable',
                'document_type' => 'required|in:original,duplicate,copy',
                'action_id' => 'required|exists:mail_actions,id',
                'priority_id' => 'required|exists:mail_priorities,id',
                'typology_id' => 'required|exists:mail_typologies,id',
                'attachments.*' => 'file|max:20480|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,mp4,mov,avi',
            ]);

            $mailCode = $this->generateMailCode( $validatedData['typology_id']);

            $mail = Mail::create($validatedData + [
                    'code' => $mailCode,
                    'sender_organisation_id' => auth()->user()->current_organisation_id,
                    'sender_user_id' => auth()->id(),
                    'status' => 'in_progress',
                ]);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $this->handleFileUpload($file, $mail);
                }
            }

            return redirect()->route('mail-send.index')
                ->with('success', 'Mail créé avec succès avec les pièces jointes.');

        } catch (Exception $e) {
            Log::error('Erreur lors de la création du mail : ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la création du mail.');
        }
    }









    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|max:150',
                'date' => 'required|date',
                'description' => 'nullable',
                'document_type' => 'required|in:original,duplicate,copy',
                'action_id' => 'required|exists:mail_actions,id',
                'recipient_user_id' => 'nullable|exists:users,id',
                'recipient_organisation_id' => 'required|exists:organisations,id',
                'priority_id' => 'required|exists:mail_priorities,id',
                'typology_id' => 'required|exists:mail_typologies,id',
                'attachments.*' => 'file|max:20480|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,mp4,mov,avi',
            ]);

            $mailCode = $this->generateMailCode( $validatedData['typology_id']);

            $mail = Mail::create($validatedData + [
                    'code' => $mailCode,
                    'sender_organisation_id' => auth()->user()->current_organisation_id,
                    'sender_user_id' => auth()->id(),
                    'status' => 'in_progress',
                ]);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $this->handleFileUpload($file, $mail);
                }
            }

            return redirect()->route('mail-send.index')
                ->with('success', 'Mail créé avec succès avec les pièces jointes.');

        } catch (Exception $e) {
            Log::error('Erreur lors de la création du mail : ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la création du mail.');
        }
    }







    public function transfer(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'mail_id' => 'required|exists:mails,id',
                'recipient_user_id' => 'required|exists:users,id',
                'recipient_organisation_id' => 'required|exists:organisations,id',
                'comment' => 'nullable|string|max:1000',
            ]);

            $originalMail = Mail::findOrFail($validatedData['mail_id']);

            $transferredMail = new Mail();

            $transferredMail->fill([
                'code' => $originalMail->code,
                'name' => $originalMail->name,
                'date' => now(),
                'description' => $originalMail->description . "\n" .
                    Auth::user()->name . " : " . now() . " : " . $validatedData['comment'],
                'document_type' => $originalMail->document_type,
                'status' => 'in_progress',
                'priority_id' => $originalMail->priority_id,
                'typology_id' => $originalMail->typology_id,
                'action_id' => $originalMail->action_id,
                'sender_user_id' => auth()->id(),
                'sender_organisation_id' => auth()->user()->current_organisation_id,
                'recipient_user_id' => $validatedData['recipient_user_id'],
                'recipient_organisation_id' => $validatedData['recipient_organisation_id'],
                'is_archived' => false
            ]);

            $transferredMail->save();

            if ($originalMail->attachments && $originalMail->attachments->count() > 0) {
                foreach ($originalMail->attachments as $attachment) {
                    $transferredMail->attachments()->attach($attachment->id, ['added_by' => auth()->id()]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Courrier transféré avec succès',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur',
            ], 500);
        }
    }


    public function show(int $id)
    {
        $mail = Mail::with([
            'action',
            'sender',
            'senderOrganisation',
            'recipient',
            'recipientOrganisation',

            'attachments'
        ])->findOrFail($id);

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
            'attachments'
        ])->findOrFail($id);

        $currentOrganisationId = Auth::user()->current_organisation_id;
        $mailActions = MailAction::orderBy('name')->get();
        $recipientOrganisations = Organisation::where('id', '!=', $currentOrganisationId)
            ->orderBy('name')
            ->get();
        $users = User::orderBy('name')->get();
        $priorities = MailPriority::orderBy('name')->get();
        $typologies = MailTypology::orderBy('name')->get();

        return view('mails.send.edit', compact(
            'mail',
            'mailActions',
            'recipientOrganisations',
            'users',
            'priorities',
            'typologies'
        ));
    }

    public function update(Request $request, int $id)
    {
        try {
            $mail = Mail::findOrFail($id);

            $validatedData = $request->validate([
                'name' => 'required|max:150',
                'date' => 'required|date',
                'description' => 'nullable',
                'document_type' => 'required|in:original,duplicate,copy',
                'action_id' => 'required|exists:mail_actions,id',
                'recipient_user_id' => 'nullable|exists:users,id',
                'recipient_organisation_id' => 'required|exists:organisations,id',
                'priority_id' => 'required|exists:mail_priorities,id',
                'typology_id' => 'required|exists:mail_typologies,id',
                'attachments.*' => 'file|max:20480|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,mp4,mov,avi',
            ]);

            $mail->update($validatedData);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $this->handleFileUpload($file, $mail);
                }
            }

            return redirect()->route('mail-send.index')
                ->with('success', 'Mail mis à jour avec succès');

        } catch (Exception $e) {
            Log::error('Erreur lors de la mise à jour du mail : ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la mise à jour du mail.');
        }
    }

    public function destroy($id)
    {
        try {
            $mail = Mail::findOrFail($id);

            // Supprimer les fichiers physiques des pièces jointes
            foreach ($mail->attachments as $attachment) {
                if (Storage::disk('public')->exists($attachment->path)) {
                    Storage::disk('public')->delete($attachment->path);
                }
                if ($attachment->thumbnail_path && Storage::disk('public')->exists($attachment->thumbnail_path)) {
                    Storage::disk('public')->delete($attachment->thumbnail_path);
                }
            }

            $mail->delete();

            return redirect()->route('mail-send.index')
                ->with('success', 'Mail supprimé avec succès');
        } catch (Exception $e) {
            Log::error('Erreur lors de la suppression du mail : ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la suppression du mail.');
        }
    }

    public function removeAttachment($mailId, $attachmentId)
    {
        try {
            $mail = Mail::findOrFail($mailId);
            $attachment = MailAttachment::findOrFail($attachmentId);

            if ($mail->sender_user_id !== auth()->id()) {
                return response()->json(['error' => 'Non autorisé'], 403);
            }

            // Supprimer le fichier physique
            if (Storage::disk('public')->exists($attachment->path)) {
                Storage::disk('public')->delete($attachment->path);
            }

            // Supprimer la miniature si elle existe
            if ($attachment->thumbnail_path && Storage::disk('public')->exists($attachment->thumbnail_path)) {
                Storage::disk('public')->delete($attachment->thumbnail_path);
            }

            // Détacher et supprimer l'attachement
            $mail->attachments()->detach($attachmentId);
            $attachment->delete();

            return response()->json(['success' => true]);

        } catch (Exception $e) {
            Log::error('Erreur lors de la suppression de la pièce jointe : ' . $e->getMessage());
            return response()->json(['error' => 'Une erreur est survenue'], 500);
        }
    }



    public function generateMailCode(int $typologie_id)
    {
        $typology = MailTypology::findOrFail($typologie_id);
        $year = date('Y');

        $count = Mail::whereYear('created_at', $year)
                ->where('mail_typology_id', $typologie_id)
                ->count();

        $nextNumber = $count + 1;
        $codeExists = true;

        while ($codeExists) {
            $formattedNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            $candidateCode = $year . "/" . $typology->code . "/" . $formattedNumber;
            $codeExists = Mail::where('code', $candidateCode)->exists();
            if ($codeExists) {
                $nextNumber++;
            }
        }

        return $candidateCode;
    }




    protected function canAccessAttachment($mail)
    {
        $user = auth()->user();

        // Vérifier si l'utilisateur est l'expéditeur
        if ($mail->sender_user_id === $user->id) {
            return true;
        }

        // Vérifier si l'utilisateur est le destinataire
        if ($mail->recipient_user_id === $user->id) {
            return true;
        }

        // Vérifier si l'utilisateur appartient à l'organisation destinataire
        if ($mail->recipient_organisation_id === $user->current_organisation_id) {
            return true;
        }

        // Vérifier si l'utilisateur appartient à l'organisation expéditrice
        if ($mail->sender_organisation_id === $user->current_organisation_id) {
            return true;
        }

        return false;
    }

    public function getAttachmentInfo($attachmentId)
    {
        try {
            $attachment = MailAttachment::findOrFail($attachmentId);

            return response()->json([
                'id' => $attachment->id,
                'name' => $attachment->name,
                'size' => $this->formatFileSize($attachment->size),
                'type' => $attachment->mime_type,
                'thumbnail_path' => $attachment->thumbnail_path,
                'created_at' => $attachment->created_at->format('d/m/Y H:i'),
                'creator' => $attachment->creator->name
            ]);

        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération des informations de la pièce jointe : ' . $e->getMessage());
            return response()->json(['error' => 'Une erreur est survenue'], 500);
        }
    }

    protected function formatFileSize($size)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }

    public function validateAttachment(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,mp4,mov,avi'
        ]);

        $file = $request->file('file');

        return response()->json([
            'valid' => true,
            'name' => $file->getClientOriginalName(),
            'size' => $this->formatFileSize($file->getSize()),
            'type' => $file->getMimeType()
        ]);
    }

    public function compressThumbnail($path)
    {
        try {
            $image = Image::make(storage_path('app/public/' . $path));

            $image->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $image->save(null, 60); // Compression à 60%

            return true;
        } catch (Exception $e) {
            Log::error('Erreur lors de la compression de la miniature : ' . $e->getMessage());
            return false;
        }
    }

    public function checkAttachmentExists($path)
    {
        return Storage::disk('public')->exists($path);
    }

    public function cleanupOrphanedAttachments()
    {
        try {
            // Trouver les pièces jointes non liées à un mail
            $orphanedAttachments = MailAttachment::whereDoesntHave('mails')->get();

            foreach ($orphanedAttachments as $attachment) {
                // Supprimer les fichiers physiques
                if ($this->checkAttachmentExists($attachment->path)) {
                    Storage::disk('public')->delete($attachment->path);
                }
                if ($attachment->thumbnail_path && $this->checkAttachmentExists($attachment->thumbnail_path)) {
                    Storage::disk('public')->delete($attachment->thumbnail_path);
                }

                // Supprimer l'enregistrement
                $attachment->delete();
            }

            Log::info('Nettoyage des pièces jointes orphelines terminé');
            return true;

        } catch (Exception $e) {
            Log::error('Erreur lors du nettoyage des pièces jointes orphelines : ' . $e->getMessage());
            return false;
        }
    }



public function inprogress()
{
    try {
        $mails = Mail::with(['action', 'sender', 'senderOrganisation', 'attachments'])
            ->where('recipient_user_id', Auth::id())
            ->where('status', 'in_progress')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('mails.received.index', compact('mails'));
    } catch (Exception $e) {
        Log::error('Erreur lors de la récupération des courriers en cours : ' . $e->getMessage());
        return back()->with('error', 'Une erreur est survenue lors du chargement des courriers.');
    }
}





public function rejected()
{
    try {
            $mails = Mail::with(['action', 'sender', 'senderOrganisation', 'attachments'])
                ->where('recipient_user_id', Auth::id())
                ->where('status', 'reject')
                ->orderBy('created_at', 'desc')
                ->get();
            return view('mails.send.index', compact('mails'));

        } catch (Exception $e) {

            Log::error('Erreur lors de la récupération des courriers en cours : ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors du chargement des courriers.');

        }
}




public function approve(Request $request)
{
    try {
        $validatedData = $request->validate(['id' => 'required|exists:mails,id']);
        $mail = Mail::findOrFail($validatedData['id']);

        $mail->update([
            'recipient_user_id' => auth()->id(),
            'status' => 'received'
        ]);

        return redirect()->route('mail-received.index')
            ->with('success', 'Courrier approuvé avec succès');
    } catch (Exception $e) {
        Log::error('Erreur lors de l\'approbation du courrier : ' . $e->getMessage());
        return back()->with('error', 'Une erreur est survenue lors de l\'approbation.');
    }
}





}
