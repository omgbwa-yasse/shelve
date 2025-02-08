<?php

namespace App\Http\Controllers;

use App\Models\BulletinBoard;
use App\Models\Attachment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use FFMpeg\FFMpeg;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class BulletinBoardAttachmentController extends Controller
{
    public function index($bulletinBoardId)
    {
        $bulletinBoard = BulletinBoard::findOrFail($bulletinBoardId);
        $attachments = $bulletinBoard->attachments;

        return view('bulletin-boards.attachments.index', compact('bulletinBoard', 'attachments'));
    }

    public function create($bulletinBoardId)
    {
        $bulletinBoard = BulletinBoard::findOrFail($bulletinBoardId);
        return view('bulletin-boards.attachments.create', compact('bulletinBoard'));
    }

    public function store(Request $request, $bulletinBoardId)
    {
        $bulletinBoard = BulletinBoard::findOrFail($bulletinBoardId);

        try {
            $request->validate([
                'name' => 'required|max:100',
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png,gif,mp4,avi,mov|max:20480', // 20MB max
                'thumbnail' => 'nullable|string',
            ]);

            $file = $request->file('file');
            $path = $file->store('bulletin_board_attachments');

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
            ]);

            if ($request->filled('thumbnail')) {
                $thumbnailData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->thumbnail));
                $thumbnailPath = 'thumbnails_bulletin_board/' . $attachment->id . '.jpg';
                $stored = Storage::disk('public')->put($thumbnailPath, $thumbnailData);

                if ($stored) {
                    $attachment->thumbnail_path = $thumbnailPath;
                    $attachment->save();
                }
            } else {
                if (in_array($fileType, ['image', 'video'])) {
                    $thumbnailPath = $this->generateThumbnail($file, $attachment->id, $fileType);
                    if ($thumbnailPath) {
                        $attachment->thumbnail_path = $thumbnailPath;
                        $attachment->save();
                    }
                }
            }

            $bulletinBoard->attachments()->attach($attachment->id, ['added_by' => auth()->id()]);

            return redirect()->route('bulletin-boards.show', $bulletinBoard)
                ->with('success', 'Pièce jointe ajoutée avec succès.');
        } catch (Exception $e) {
            Log::error('Erreur lors de l\'ajout de la pièce jointe : ' . $e->getMessage());
            Log::error('Stack trace : ' . $e->getTraceAsString());
            return redirect()->route('bulletin-boards.show', $bulletinBoard)
                ->with('error', 'Une erreur est survenue lors de l\'ajout de la pièce jointe.');
        }
    }

    private function generateThumbnail($file, $attachmentId, $fileType)
    {
        $thumbnailPath = 'thumbnails_bulletin_board/' . $attachmentId . '.jpg';

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

    public function show(int $bulletinBoardId, int $attachmentId)
    {
        $bulletinBoard = BulletinBoard::findOrFail($bulletinBoardId);
        $attachment = Attachment::findOrFail($attachmentId);
        return view('bulletin-boards.attachments.show', compact('bulletinBoard', 'attachment'));
    }

    public function destroy(int $bulletinBoardId, int $attachmentId)
    {
        $attachment = Attachment::findOrFail($attachmentId);
        $bulletinBoard = BulletinBoard::findOrFail($bulletinBoardId);

        $bulletinBoard->attachments()->detach($attachment->id);

        return redirect()->route('bulletin-boards.show', $bulletinBoard->id)
            ->with('success', 'Pièce jointe supprimée avec succès.');
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
}
