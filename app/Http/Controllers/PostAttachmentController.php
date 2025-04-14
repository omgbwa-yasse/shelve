<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Attachment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use FFMpeg\FFMpeg;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class PostAttachmentController extends Controller
{
    public function index($id)
    {
        $post = Post::findOrFail($id);
        $attachments = $post->attachments;

        return view('bulletin-boards.posts.attachments.index', compact('post', 'attachments'));
    }

    public function create($id)
    {
        $post = Post::findOrFail($id);
        return view('bulletin-boards.posts.attachments.create', compact('post'));
    }

    public function store(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        try {
            $request->validate([
                'name' => 'required|max:100',
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png,gif,mp4,avi,mov|max:20480', // 20MB max
                'thumbnail' => 'nullable|string',
            ]);

            $file = $request->file('file');
            $path = $file->store('post_attachments');

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
                'type' => 'bulletinboardpost',
            ]);

            if ($request->filled('thumbnail')) {
                $thumbnailData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->thumbnail));
                $thumbnailPath = 'thumbnails_post/' . $attachment->id . '.jpg';
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

            $post->attachments()->attach($attachment->id, ['created_by' => auth()->id()]);

            return redirect()->route('posts.show', $post)->with('success', 'Pièce jointe ajoutée avec succès à la publication.');
        } catch (Exception $e) {
            Log::error('Erreur lors de l\'ajout de la pièce jointe à la publication : ' . $e->getMessage());
            Log::error('Stack trace : ' . $e->getTraceAsString());
            return redirect()->route('posts.show', $post)->with('error', 'Une erreur est survenue lors de l\'ajout de la pièce jointe à la publication.');
        }
    }

    private function generateThumbnail($file, $attachmentId, $fileType)
    {
        $thumbnailPath = 'thumbnails_post/' . $attachmentId . '.jpg';

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

    public function show(int $postId, int $attachmentId)
    {
        $post = Post::findOrFail($postId);
        $attachment = Attachment::findOrFail($attachmentId);
        return view('bulletin-boards.posts.attachments.show', compact('post', 'attachment'));
    }

    public function destroy(int $postId, int $attachmentId)
    {
        $attachment = Attachment::findOrFail($attachmentId);
        $post = Post::findOrFail($postId);

        $post->attachments()->detach($attachment->id);

        Storage::delete($attachment->path);

        if ($attachment->thumbnail_path) {
            Storage::disk('public')->delete($attachment->thumbnail_path);
        }

        $attachment->delete();

        return redirect()->route('posts.show', $post->id)->with('success', 'Pièce jointe supprimée avec succès.');
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
