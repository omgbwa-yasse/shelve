<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RecordAttachment;
use App\Models\Record;
use App\Models\Attachment;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
        try {
            $request->validate([
                'name' => 'required|max:100',
                'file' => 'required|file|mimes:pdf|max:2048',
                'thumbnail' => 'nullable|string',
            ]);

            $record = Record::findOrFail($id);
            $file = $request->file('file');

            $path = $file->store('attachments');

            $attachment = Attachment::create([
                'path' => $path,
                'name' => $request->input('name'),
                'crypt' => md5_file($file),
                'crypt_sha512' => hash_file('sha512', $file->getRealPath()),
                'size' => $file->getSize(),
                'creator_id' => auth()->id(),
                'type' => 'record',
            ]);

            if ($request->filled('thumbnail')) {
                $thumbnailData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->thumbnail));
                $thumbnailPath = 'thumbnails_record/' . $attachment->id . '.jpg';
                $stored = Storage::disk('public')->put($thumbnailPath, $thumbnailData);

                if ($stored) {
                    $attachment->update(['thumbnail_path' => $thumbnailPath]);
                }
            }

            $record->attachments()->attach($attachment->id);
            return redirect()->route('records.attachments.index', $record->id)->with('success', 'Attachment created successfully.');

        } catch (Exception $e) {
            Log::error('Erreur lors de l\'ajout de la pièce jointe : ' . $e->getMessage());
            return redirect()->route('records.attachments.index', $record->id)->with('success', 'Attachment created successfully.');
        }
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


