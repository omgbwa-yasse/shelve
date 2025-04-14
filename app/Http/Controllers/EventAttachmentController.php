<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Attachment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use FFMpeg\FFMpeg;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class EventAttachmentController extends Controller
{
    public function index($id)
    {
        $event = Event::findOrFail($id);
        $attachments = $event->attachments;

        return view('bulletin-boards.events.attachments.index', compact('event', 'attachments'));
    }

    public function create($id)
    {
        $event = Event::findOrFail($id);
        return view('bulletin-boards.events.attachments.create', compact('event'));
    }

    public function store(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        try {
            $request->validate([
                'name' => 'required|max:100',
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png,gif,mp4,avi,mov|max:20480', // 20MB max
                'thumbnail' => 'nullable|string',
            ]);

            $file = $request->file('file');
            $path = $file->store('event_attachments');

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
                'type' => 'bulletinboardevent',
            ]);

            if ($request->filled('thumbnail')) {
                $thumbnailData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->thumbnail));
                $thumbnailPath = 'thumbnails_event/' . $attachment->id . '.jpg';
                $stored = Storage::disk('public')->put($thumbnailPath, $thumbnailData);

                if ($stored) {
                    $attachment->thumbnail_path = $thumbnailPath;
                    $attachment->save();
                }
            } else {
                // Generate thumbnail for images and videos if not provided
                if (in_array($fileType, ['image', 'video'])) {
                    $thumbnailPath = $this->generateThumbnail($file, $attachment->id, $fileType);
                    if ($thumbnailPath) {
                        $attachment->thumbnail_path = $thumbnailPath;
                        $attachment->save();
                    }
                }
            }

            $event->attachments()->attach($attachment->id, ['created_by' => auth()->id()]);

            return redirect()->route('events.show', $event)->with('success', 'Pièce jointe ajoutée avec succès à l\'événement.');
        } catch (Exception $e) {
            Log::error('Erreur lors de l\'ajout de la pièce jointe à l\'événement : ' . $e->getMessage());
            Log::error('Stack trace : ' . $e->getTraceAsString());
            return redirect()->route('events.show', $event)->with('error', 'Une erreur est survenue lors de l\'ajout de la pièce jointe à l\'événement.');
        }
    }

    private function generateThumbnail($file, $attachmentId, $fileType)
    {
        $thumbnailPath = 'thumbnails_event/' . $attachmentId . '.jpg';

        try {
            if ($fileType === 'image') {
                $img = Image::make($file->getRealPath());
                $img->fit(300, 300);
                $img->save(storage_path('app/public/' . $thumbnailPath));
            } elseif ($fileType === 'video') {
                $ffmpeg = FFMpeg::create();
                $video = $ffmpeg->open($file->getRealPath());
                $frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(1));
                $frame->save(storage_path('app/public/' . $thumbnailPath));
            } else {
                return null;
            }
        } catch (Exception $e) {
            Log::error('Erreur lors de la génération de la miniature : ' . $e->getMessage());
            return null;
        }

        return $thumbnailPath;
    }

    public function show(int $eventId, int $attachmentId)
    {
        $event = Event::findOrFail($eventId);
        $attachment = Attachment::findOrFail($attachmentId);
        return view('bulletin-boards.events.attachments.show', compact('event', 'attachment'));
    }

    public function destroy(int $eventId, int $attachmentId)
    {
        $attachment = Attachment::findOrFail($attachmentId);
        $event = Event::findOrFail($eventId);

        $event->attachments()->detach($attachment->id);

        // Suppression du fichier physique
        Storage::delete($attachment->path);

        // Suppression de la vignette si elle existe
        if ($attachment->thumbnail_path) {
            Storage::disk('public')->delete($attachment->thumbnail_path);
        }

        $attachment->delete();

        return redirect()->route('events.show', $event->id)->with('success', 'Pièce jointe supprimée avec succès.');
    }

    public function download($id)
    {
        $attachment = Attachment::findOrFail($id);
        $filePath = storage_path('app/' . $attachment->path);

        if (file_exists($filePath)) {
            $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
            $fileName = $attachment->name . '.' . $fileExtension;
            return response()->download($filePath, $fileName);
        }

        return abort(404);
    }

    public function preview($id)
    {
        $attachment = Attachment::findOrFail($id);
        $path = storage_path('app/' . $attachment->path);

        if (!File::exists($path)) {
            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }


                /**
             * Retourne la liste partielle des pièces jointes pour les requêtes AJAX
             */
            public function getAttachmentsList($eventId)
            {
                $event = Event::findOrFail($eventId);
                $attachments = $event->attachments;

                if (request()->ajax()) {
                    return response()->view('bulletin-boards.events.attachments.partials.list', compact('event', 'attachments'));
                }

                return redirect()->route('events.attachments.index', $eventId);
            }

            /**
             * Gestion AJAX pour l'upload de fichiers
             */
            public function ajaxStore(Request $request, $eventId)
            {
                $event = Event::findOrFail($eventId);

                $request->validate([
                    'files.*' => 'required|file|mimes:pdf,jpg,jpeg,png,gif,mp4,avi,mov,doc,docx,xls,xlsx|max:20480',
                    'name' => 'nullable|string|max:100',
                ]);

                $results = [];

                if ($request->hasFile('files')) {
                    foreach ($request->file('files') as $file) {
                        $path = $file->store('event_attachments');

                        $mimeType = $file->getMimeType();
                        $fileType = explode('/', $mimeType)[0];

                        $name = $request->input('name') ?: $file->getClientOriginalName();

                        $attachment = Attachment::create([
                            'path' => $path,
                            'name' => $name,
                            'crypt' => md5_file($file),
                            'crypt_sha512' => hash_file('sha512', $file->getRealPath()),
                            'size' => $file->getSize(),
                            'creator_id' => auth()->id(),
                            'mime_type' => $mimeType,
                            'type' => 'event',
                        ]);

                        // Générer une vignette si nécessaire
                        if (in_array($fileType, ['image', 'video'])) {
                            $thumbnailPath = $this->generateThumbnail($file, $attachment->id, $fileType);
                            if ($thumbnailPath) {
                                $attachment->thumbnail_path = $thumbnailPath;
                                $attachment->save();
                            }
                        }

                        $event->attachments()->attach($attachment->id, ['created_by' => auth()->id()]);

                        $results[] = [
                            'id' => $attachment->id,
                            'name' => $attachment->name,
                            'size' => $attachment->size,
                            'type' => $mimeType,
                            'success' => true
                        ];
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Fichiers téléversés avec succès',
                    'files' => $results
                ]);
            }

            /**
             * Supprimer une pièce jointe via AJAX
             */
            public function ajaxDestroy($eventId, $attachmentId)
            {
                $event = Event::findOrFail($eventId);
                $attachment = Attachment::findOrFail($attachmentId);

                // Vérifier les autorisations
                if (!auth()->user()->can('delete', $attachment) && !auth()->user()->can('update', $event)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Action non autorisée'
                    ], 403);
                }

                // Détacher la pièce jointe de l'événement
                $event->attachments()->detach($attachment->id);

                // Supprimer le fichier physique
                Storage::delete($attachment->path);

                // Supprimer la vignette si elle existe
                if ($attachment->thumbnail_path) {
                    Storage::disk('public')->delete($attachment->thumbnail_path);
                }

                // Supprimer l'enregistrement de la pièce jointe
                $attachment->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Pièce jointe supprimée avec succès'
                ]);
            }
}
