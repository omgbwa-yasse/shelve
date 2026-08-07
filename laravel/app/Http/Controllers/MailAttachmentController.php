<?php

namespace App\Http\Controllers;

use App\Models\Mail;
use App\Models\Attachment; // Utiliser le modèle Attachment directement
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use FFMpeg\FFMpeg;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class MailAttachmentController extends Controller
{
public function index($id)
{
$mail = Mail::findOrFail($id);
$attachments = $mail->attachments; // La relation "attachments" existe déjà dans le modèle Mail

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

        $attachment = Attachment::create([ // Utiliser le modèle Attachment
            'path' => $path,
            'name' => $request->input('name'),
            'crypt' => md5_file($file),
            'crypt_sha512' => hash_file('sha512', $file->getRealPath()),
            'size' => $file->getSize(),
            'creator_id' => auth()->id(),
            'mime_type' => $mimeType,
            'type' => 'mail',
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

        $mail->attachments()->attach($attachment->id, ['added_by' => auth()->id()]); // Ajouter la relation avec la colonne 'added_by'

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

public function show(int $mailid, int $attachmentid)
{
    $mail = Mail::findOrFail($mailid);
    $attachment = Attachment::findOrFail($attachmentid); // Utiliser le modèle Attachment
    return view('mails.attachments.show', compact('mail', 'attachment'));
}

public function destroy(int $mailid, int $attachmentid)
{
    $attachment = Attachment::findOrFail($attachmentid); // Utiliser le modèle Attachment
    $mail = Mail::findOrFail($mailid);

    $mail->attachments()->detach($attachment->id);

    $attachment->delete();

    return redirect()->route('mails.show', $mail->id)->with('success', 'Pièce jointe supprimée avec succès.');
}

public function download($id)
{
    $attachment = Attachment::findOrFail($id); // Utiliser le modèle Attachment
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
    $attachment = Attachment::findOrFail($id); // Utiliser le modèle Attachment
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
}
