<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RecordAttachment;
use App\Models\Record;
use App\Models\Attachment;
use Intervention\Image\Image;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

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
//        dd( $request);
        try {
            $request->validate([
                'name' => 'required|max:100',
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png,gif,mp4,avi,mov|max:20480', // 20MB max
                'thumbnail' => 'nullable|string',
            ]);

            $record = Record::findOrFail($id);
            $file = $request->file('file');

            $path = $file->store('attachments');

            $mimeType = $file->getMimeType();
            $fileType = explode('/', $mimeType)[0];

            $attachment = Attachment::create([
                'path' => $path,
                'name' => $request->input('name'),
                'crypt' => md5_file($file),
                'crypt_sha512' => hash_file('sha512', $file->getRealPath()),
                'size' => $file->getSize(),
                'creator_id' => auth()->id(),
                'mime_type' => $mimeType,
                'type' => 'record',
            ]);

            if ($request->filled('thumbnail')) {
                $thumbnailData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->thumbnail));
                $thumbnailPath = 'thumbnails_record/' . $attachment->id . '.jpg';
                $stored = Storage::disk('public')->put($thumbnailPath, $thumbnailData);

                if ($stored) {
                    $attachment->update(['thumbnail_path' => $thumbnailPath]);
                }
            } else {
                // Generate thumbnail for images and videos if not provided
                if (in_array($fileType, ['image', 'video'])) {
                    $thumbnailPath = $this->generateThumbnail($file, $attachment->id, $fileType);
                    if ($thumbnailPath) {
                        $attachment->update(['thumbnail_path' => $thumbnailPath]);
                    }
                }
            }

            $record->attachments()->attach($attachment->id);

            // Vérifier si c'est une requête AJAX
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Attachment créé avec succès',
                    'attachment' => [
                        'id' => $attachment->id,
                        'name' => $attachment->name,
                        'size' => $attachment->size,
                        'path' => $attachment->path
                    ]
                ]);
            }

            return redirect()->route('records.attachments.index', $record->id)->with('success', 'Attachment created successfully.');

        } catch (\Exception $e) {
            // Log l'erreur
            \Log::error('Erreur dans RecordAttachmentController@store: ' . $e->getMessage());
            // Retournez l'erreur pour le débogage
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function generateThumbnail($file, $attachmentId, $fileType)
    {
        $thumbnailPath = 'thumbnails_record/' . $attachmentId . '.jpg';

        if ($fileType === 'image') {
            $img = Image::make($file->getRealPath());
            $img->fit(300, 300);
            $img->save(storage_path('app/public/' . $thumbnailPath));
        } elseif ($fileType === 'video') {
            $ffmpeg = FFMpeg::create();
            $video = $ffmpeg->open($file->getRealPath());
            $frame = $video->frame(TimeCode::fromSeconds(1));
            $frame->save(storage_path('app/public/' . $thumbnailPath));
        } else {
            return null;
        }

        return $thumbnailPath;
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

    /**
     * Télécharger un attachment temporaire (sans l'associer à un record)
     */
    public function uploadTemp(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:pdf,txt,docx,doc,rtf,odt,jpg,jpeg,png,gif|max:10240', // 10MB max
            ]);

            $file = $request->file('file');
            $path = $file->store('attachments/temp');

            $attachment = Attachment::create([
                'path' => $path,
                'name' => $request->input('name', $file->getClientOriginalName()),
                'crypt' => md5_file($file->getRealPath()),
                'crypt_sha512' => hash_file('sha512', $file->getRealPath()),
                'size' => $file->getSize(),
                'creator_id' => Auth::id(),
                'mime_type' => $file->getMimeType(),
                'type' => 'record',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Fichier téléchargé avec succès',
                'attachment' => [
                    'id' => $attachment->id,
                    'name' => $attachment->name,
                    'size' => $attachment->size,
                    'path' => $attachment->path
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement temporaire:', [
                'error' => $e->getMessage(),
                'file' => $request->file('file')?->getClientOriginalName()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du téléchargement: ' . $e->getMessage()
            ], 500);
        }
    }
}


