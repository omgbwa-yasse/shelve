<?php
namespace App\Http\Controllers;

use App\Models\Mail;
use App\Models\MailAttachment;
use Illuminate\Http\Request;

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

        // Traitement du fichier téléchargé
        $filePath = $request->file('file')->store('attachments');
        $fileSize = $request->file('file')->getSize();
        $fileCrypt = md5_file($request->file('file')->getRealPath());

        $attachment = MailAttachment::create([
            'path' => $filePath,
            'name' => $validatedData['name'],
            'crypt' => $fileCrypt,
            'size' => $fileSize,
            'creator_id' => auth()->id(),
        ]);

        $mail->attachments()->attach($attachment->id);

        return redirect()->route('mail-attachment.index', $mail->id)->with('success', 'MailAttachment created successfully.');
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
}
