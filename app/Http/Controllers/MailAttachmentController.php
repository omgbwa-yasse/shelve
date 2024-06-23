<?php

namespace App\Http\Controllers;
use App\Models\Mail;
use App\Models\MailAttachment;
use Illuminate\Http\Request;

class MailAttachmentController extends Controller
{

    public function index(Mail $mail)
    {
        $attachments = $mail->attachments;
        return view('mails.attachments.index', compact('mail', 'attachments'));
    }



    public function create(Mail $mail)
    {
        return view('mails.attachments.create', compact('mail'));
    }



    public function store(Request $request, Mail $mail)
    {
        $validatedData = $request->validate([
            'path' => 'required|max:100',
            'name' => 'required|max:100',
            'crypt' => 'required|max:255',
            'size' => 'required|integer',
        ]);

        $attachment = MailAttachment::create($validatedData + [
            'creator_id' => auth()->id(),
        ]);

        $mail->attachments()->attach($attachment->id);

        return redirect()->route('mail-attachment.index', $mail)->with('success', 'MailAttachment created successfully.');
    }



    public function show(Mail $mail, MailAttachment $attachment)
    {
        return view('mails.attachments.show', compact('mail', 'attachment'));
    }



    public function destroy(Mail $mail, MailAttachment $attachment)
    {
        $mail->attachments()->detach($attachment->id);
        $attachment->delete();

        return redirect()->route('mail-attachment.index', $mail)->with('success', 'MailAttachment deleted successfully.');
    }




}
