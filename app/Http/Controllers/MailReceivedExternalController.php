<?php

namespace App\Http\Controllers;

use App\Models\Mail;
use App\Models\User;
use App\Models\MailTypology;
use App\Models\MailPriority;
use App\Models\MailAction;
use App\Models\Organisation;
use App\Models\ExternalContact;
use App\Models\ExternalOrganization;
use App\Models\MailAttachment;
use App\Models\Dolly;
use App\Enums\MailStatusEnum;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use FFMpeg\FFMpeg;

class MailReceivedExternalController extends Controller
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

    /**
     * Display a listing of incoming mails.
     */
    public function index()
    {
        return app(MailController::class)->index('received_external');
    }

    /**
     * Show the form for creating a new incoming mail.
     */
    public function create()
    {
        $typologies = MailTypology::orderBy('name')->get();
        $priorities = MailPriority::orderBy('duration')->get();
        $actions = MailAction::orderBy('name')->get();
        $senderOrganisations = Organisation::orderBy('name')->get();
        $externalContacts = ExternalContact::with('organization')->orderBy('last_name')->get();
        $externalOrganizations = ExternalOrganization::orderBy('name')->get();

        return view('mails.received.external.create', compact(
            'typologies',
            'priorities',
            'actions',
            'senderOrganisations',
            'externalContacts',
            'externalOrganizations'
        ));
    }

    /**
     * Store a newly created incoming mail in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|max:150',
                'date' => 'required|date',
                'description' => 'nullable',
                'document_type' => 'required|in:original,duplicate,copy',
                'typology_id' => 'required|exists:mail_typologies,id',
                'priority_id' => 'nullable|exists:mail_priorities,id',
                'action_id' => 'nullable|exists:mail_actions,id',
                'sender_type' => 'required|in:external_contact,external_organization,organisation',
                'external_sender_id' => 'nullable|exists:external_contacts,id',
                'external_sender_organization_id' => 'nullable|exists:external_organizations,id',
                'sender_organisation_id' => 'nullable|exists:organisations,id',
                'delivery_method' => 'nullable|string|max:50',
                'tracking_number' => 'nullable|string|max:100',
                'deadline' => 'nullable|date|after:today',
                'estimated_processing_time' => 'nullable|integer|min:1',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|max:20480|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,mp4,mov,avi',
            ]);

            // Validation conditionnelle selon le type d'expéditeur
            if ($validatedData['sender_type'] === 'external_contact' && empty($validatedData['external_sender_id'])) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['external_sender_id' => 'Veuillez sélectionner un contact externe.']);
            }

            if ($validatedData['sender_type'] === 'external_organization' && empty($validatedData['external_sender_organization_id'])) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['external_sender_organization_id' => 'Veuillez sélectionner une organisation externe.']);
            }

            if ($validatedData['sender_type'] === 'organisation' && empty($validatedData['sender_organisation_id'])) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['sender_organisation_id' => 'Veuillez sélectionner une organisation.']);
            }

            $mailCode = $this->generateMailCode($validatedData['typology_id']);

            $mail = Mail::create($validatedData + [
                'code' => $mailCode,
                'recipient_organisation_id' => Auth::user()->current_organisation_id,
                'recipient_user_id' => Auth::id(),
                'recipient_type' => 'organisation',
                'status' => MailStatusEnum::TRANSMITTED,
                'mail_type' => Mail::TYPE_INCOMING,
            ]);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $this->handleFileUpload($file, $mail);
                }
            }

            // Initialize workflow for the mail
            $mail->initializeWorkflow();

            // Log the action
            $mail->logAction('created', null, null, null, 'Courrier entrant externe créé');

            return redirect()->route('mails.received.external.index')
                ->with('success', 'Courrier entrant externe créé avec succès');

        } catch (Exception $e) {
            Log::error('Erreur lors de la création du courrier entrant externe : ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la création du courrier entrant externe.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return app(MailController::class)->show('received_external', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $mail = Mail::with([
            'typology',
            'priority',
            'action',
            'attachments',
            'sender',
            'senderOrganisation',
            'recipient',
            'recipientOrganisation',
            'externalSender',
            'externalSenderOrganization',
            'externalRecipient',
            'externalRecipientOrganization'
        ])->findOrFail($id);

        $typologies = MailTypology::orderBy('name')->get();
        $priorities = MailPriority::orderBy('duration')->get();
        $actions = MailAction::orderBy('name')->get();
        $senderOrganisations = Organisation::orderBy('name')->get();
        $externalContacts = ExternalContact::with('organization')->orderBy('last_name')->get();
        $externalOrganizations = ExternalOrganization::orderBy('name')->get();

        return view('mails.received.external.edit', compact(
            'mail',
            'typologies',
            'priorities',
            'actions',
            'senderOrganisations',
            'externalContacts',
            'externalOrganizations'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $mail = Mail::findOrFail($id);

            $validatedData = $request->validate([
                'code' => 'required|exists:mails,code,' . $mail->id,
                'name' => 'required|max:150',
                'date' => 'required|date',
                'description' => 'nullable',
                'document_type' => 'required|in:original,duplicate,copy',
                'typology_id' => 'required|exists:mail_typologies,id',
                'priority_id' => 'nullable|exists:mail_priorities,id',
                'action_id' => 'nullable|exists:mail_actions,id',
                'sender_type' => 'required|in:external_contact,external_organization,organisation',
                'external_sender_id' => 'nullable|exists:external_contacts,id',
                'external_sender_organization_id' => 'nullable|exists:external_organizations,id',
                'sender_organisation_id' => 'nullable|exists:organisations,id',
                'delivery_method' => 'nullable|string|max:50',
                'tracking_number' => 'nullable|string|max:100',
                'deadline' => 'nullable|date|after:today',
                'estimated_processing_time' => 'nullable|integer|min:1',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|max:20480|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,mp4,mov,avi',
            ]);

            $mail->update($validatedData + [
                'recipient_organisation_id' => Auth::user()->current_organisation_id,
                'recipient_user_id' => Auth::id(),
                'recipient_type' => 'organisation',
                'mail_type' => Mail::TYPE_INCOMING,
            ]);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $this->handleFileUpload($file, $mail);
                }
            }

            // Log the update action
            $mail->logAction('updated', null, null, null, 'Courrier entrant externe mis à jour');

            return redirect()->route('mails.received.external.index')
                ->with('success', 'Courrier entrant externe mis à jour avec succès');

        } catch (Exception $e) {
            Log::error('Erreur lors de la mise à jour du courrier entrant externe : ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la mise à jour du courrier entrant externe.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $mail = Mail::findOrFail($id);
            $mailCode = $mail->code;

            // Log the deletion action before deleting
            $mail->logAction('deleted', null, null, null, 'Courrier supprimé');

            // Supprimer les fichiers physiques des pièces jointes
            foreach ($mail->attachments as $attachment) {
                if (Storage::exists($attachment->path)) {
                    Storage::delete($attachment->path);
                }
                if ($attachment->thumbnail_path && Storage::exists($attachment->thumbnail_path)) {
                    Storage::delete($attachment->thumbnail_path);
                }
            }

            $mail->delete();

            return redirect()->route('mails.received.external.index')
                ->with('success', "Courrier externe {$mailCode} supprimé avec succès");

        } catch (Exception $e) {
            Log::error('Erreur lors de la suppression du courrier : ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la suppression du courrier.');
        }
    }

    /**
     * Gère le téléchargement des fichiers joints
     */
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
                'creator_id' => Auth::id(),
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
                'added_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Erreur lors du téléchargement du fichier : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Génère une miniature pour les fichiers image et vidéo
     */
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

    /**
     * Génère un code unique pour le courrier
     */
    protected function generateMailCode(int $typologie_id)
    {
        $typology = MailTypology::findOrFail($typologie_id);
        $year = date('Y');

        $count = Mail::whereYear('created_at', $year)
                ->where('typology_id', $typologie_id)
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
}
