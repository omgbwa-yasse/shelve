<?php
namespace App\Http\Controllers;

use App\Models\Mail;
use App\Models\MailAttachment;
use Illuminate\Http\Request;
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





    public function store(Request $request, $file)
    {
        $mail = Mail::findOrFail($file);

        $validatedData = $request->validate([
            'name' => 'required|max:100',
            'file' => 'required|file|mimes:pdf',
        ]);

        $filePath = $request->file('file')->store('attachments');
        $fileSize = $request->file('file')->getSize();
        $fileCrypt = md5_file($request->file('file')->getRealPath());
        $fileCryptSha512 = hash_file('sha512', $request->file('file')->getRealPath());

        $attachment = MailAttachment::create([
            'path' => $filePath,
            'name' => $validatedData['name'],
            'crypt' => $fileCrypt,
            'crypt_sha512' => $fileCryptSha512,
            'size' => $fileSize,
            'type' => 'mail',
            'creator_id' => auth()->id(),
        ]);

        $mail->attachments()->attach($attachment->id);

        return redirect()->route('mails.show', $mail)->with('success', 'MailAttachment created successfully.');
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
