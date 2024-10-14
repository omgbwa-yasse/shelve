<?php
namespace App\Http\Controllers;

use App\Models\Mail;
use App\Models\MailAttachment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Imagick;
use Intervention\Image\Image;
use FFMpeg;

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
        $mail = Mail::findOrFail($id);

        try {
            $request->validate([
                'name' => 'required|max:100',
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png,gif,mp4,avi,mov|max:20480', // 20MB max
                'thumbnail' => 'nullable|string',
            ]);

            $file = $request->file('file');
            $path = $file->store('mail_attachments');

            $mimeType = $file->getMimeType();
            $fileType = explode('/', $mimeType)[0];

            $attachment = MailAttachment::create([
                'path' => $path,
                'name' => $request->input('name'),
                'crypt' => md5_file($file),
                'crypt_sha512' => hash_file('sha512', $file->getRealPath()),
                'size' => $file->getSize(),
                'creator_id' => auth()->id(),
                'type' => $fileType,
                'mime_type' => $mimeType,
            ]);

            if ($request->filled('thumbnail')) {
                $thumbnailData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->thumbnail));
                $thumbnailPath = 'thumbnails_mail/' . $attachment->id . '.jpg';
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

            $mail->attachments()->attach($attachment->id);

            return redirect()->route('mails.show', $mail)->with('success', 'Pièce jointe ajoutée avec succès au mail.');
        } catch (Exception $e) {
            Log::error('Erreur lors de l\'ajout de la pièce jointe au mail : ' . $e->getMessage());
            Log::error('Stack trace : ' . $e->getTraceAsString());
            return redirect()->route('mails.show', $mail)->with('error', 'Une erreur est survenue lors de l\'ajout de la pièce jointe au mail.');
        }
    }

    private function generateThumbnail($file, $attachmentId, $fileType)
    {
        $thumbnailPath = 'thumbnails_mail/' . $attachmentId . '.jpg';

        if ($fileType === 'image') {
            $img = Image::make($file->getRealPath());
            $img->fit(300, 300);
            $img->save(storage_path('app/public/' . $thumbnailPath));
        } elseif ($fileType === 'video') {
            $ffmpeg = FFMpeg\FFMpeg::create();
            $video = $ffmpeg->open($file->getRealPath());
            $frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(1));
            $frame->save(storage_path('app/public/' . $thumbnailPath));
        } else {
            return null;
        }

        return $thumbnailPath;
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
            $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
            $mimeType = $this->getMimeType($fileExtension);

            return response()->file($filePath, ['Content-Type' => $mimeType]);
        }

        return abort(404);
    }

    private function getMimeType($extension)
    {
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'ogg' => 'video/ogg',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }



}
