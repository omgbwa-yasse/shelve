<?php
namespace App\Http\Controllers;

use App\Models\Mail;
use App\Models\MailAttachment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Imagick;

class MailAttachmentController extends Controller
{
    public function index($id)
    {
        $mail = Mail::findOrFail($id);
        $attachments = $mail->attachments;
        return view('mails.attachments.index', compact('mail', 'attachments'));
    }

    public function create($id)
    {
        $mail = Mail::findOrFail($id);
        return view('mails.attachments.create', compact('mail'));
    }





    public function store(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|max:100',
                'file' => 'required|file|mimes:pdf|max:2048',
                'thumbnail' => 'nullable|string',
            ]);

            $mail = Mail::findOrFail($id);
            $file = $request->file('file');

            $path = $file->store('mail_attachments');

            $attachment = MailAttachment::create([
                'path' => $path,
                'name' => $request->input('name'),
                'crypt' => md5_file($file),
                'crypt_sha512' => hash_file('sha512', $file->getRealPath()),
                'size' => $file->getSize(),
                'creator_id' => auth()->id(),
                'type' => 'mail',
            ]);

            if ($request->filled('thumbnail')) {
                $thumbnailData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->thumbnail));
                $thumbnailPath = 'thumbnails_mail/' . $attachment->id . '.jpg';
                $stored = Storage::disk('public')->put($thumbnailPath, $thumbnailData);

                if ($stored) {
                    $attachment->update(['thumbnail_path' => $thumbnailPath]);
                }
            }

            $mail->attachments()->attach($attachment->id);
            return redirect()->route('mails.show', $mail)->with('success', 'MailAttachment created successfully.');
//            return response()->json(['success' => true, 'message' => 'Pièce jointe ajoutée avec succès au mail.']);
        } catch (Exception $e) {
            Log::error('Erreur lors de l\'ajout de la pièce jointe au mail : ' . $e->getMessage());
            return redirect()->route('mails.show', $mail)->with('success', 'MailAttachment created successfully.');
//            return response()->json(['success' => false, 'message' => 'Une erreur est survenue lors de l\'ajout de la pièce jointe au mail.'], 500);
        }
    }





    public function show($id, MailAttachment $attachment)
    {
        $mail = Mail::findOrFail($id);
        return view('mails.attachments.show', compact('mail', 'attachment'));
    }

    public function destroy(Mail $mail, MailAttachment $attachment)
    {
        $mail->attachments()->detach($attachment->id);
        $attachment->delete();

        return redirect()->route('mail-attachment.index', $mail)->with('success', 'MailAttachment deleted successfully.');
    }
    public function download($id)
    {
        $attachment = MailAttachment::findOrFail($id);
        $filePath = storage_path('app/' . $attachment->path);

        if (file_exists($filePath)) {
            // Obtenez l'extension du fichier à partir du chemin
            $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
            $fileName = $attachment->name . '.' . $fileExtension;
//dd( $fileExtension,$filePath);
            return response()->download($filePath, $fileName);
        }

        return abort(404);
    }
    public function preview($id)
    {
        $attachment = MailAttachment::findOrFail($id);
        $filePath = storage_path('app/' . $attachment->path);

        if (file_exists($filePath)) {
            return response()->file($filePath);
        }

        return abort(404);
    }



}
