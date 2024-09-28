<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RecordAttachment;
use App\Models\Record;
use App\Models\Attachment;

use Illuminate\Http\Request;

class RecordAttachmentController extends Controller
{
    public function index(Record $record)
    {
        $attachments = $record->attachments;
        return view('records.attachments.index', compact('record', 'attachments'));
    }

    public function create($id)
    {
        $record = Record::findOrFail($id);
        return view('records.attachments.create', compact('record'));
    }

    public function store(Request $request, $id)
    {
        $record = Record::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|max:100',
            'file' => 'required|file|mimes:pdf',
        ]);

        // Traitement du fichier téléchargé
        $filePath = $request->file('file')->store('attachments');
        $fileSize = $request->file('file')->getSize();
        $fileCrypt = md5_file($request->file('file')->getRealPath());

        $attachment = Attachment::create([
            'path' => $filePath,
            'name' => $validatedData['name'],
            'crypt' => $fileCrypt,
            'size' => $fileSize,
            'creator_id' => auth()->id(),
            'type' => 'record',
        ]);

        $record->attachments()->attach($attachment->id);

        return redirect()->route('records.attachments.index', $record->id)->with('success', 'Attachment created successfully.');
    }

    public function edit(Record $record, Attachment $attachment)
    {
        return view('records.attachments.edit', compact('record', 'attachment'));
    }

    public function update(Request $request, Record $record, Attachment $attachment)
    {
        $request->validate([
            'file_path' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $attachment->update($request->all());

        return redirect()->route('records.attachments.index', $record);
    }

    public function destroy(Record $record, Attachment $attachment)
    {
        $attachment->delete();

        return redirect()->route('records.attachments.index', $record);
    }

    public function show(Record $record, Attachment $attachment)
    {
        return view('records.attachments.show', compact('record', 'attachment'));
    }
    public function download($id)
    {
        $attachment = RecordAttachment::findOrFail($id);
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
        $attachment = RecordAttachment::findOrFail($id);
        $filePath = storage_path('app/' . $attachment->path);

        if (file_exists($filePath)) {
            return response()->file($filePath);
        }

        return abort(404);
    }

}


