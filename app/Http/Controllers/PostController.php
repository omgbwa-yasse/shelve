<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\BulletinBoard;
use App\Models\Post;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Image;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;

class PostController extends Controller
{
    public function index(BulletinBoard $BulletinBoard)
    {
        $posts = Post::with(['bulletinBoard', 'user'])
            ->when(request('search'), function($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when(request('organisation'), function($query, $organisationId) {
                return $query->whereHas('bulletinBoard.organisations', function($q) use ($organisationId) {
                    $q->where('organisations.id', $organisationId);
                });
            })
            ->latest()
            ->paginate(10);

        $organisations = Organisation::all();

        return view('bulletin-boards.posts.index', compact('posts', 'organisations'));
    }

    public function create(BulletinBoard $BulletinBoard)
    {
        $organisations = Organisation::all();
        return view('bulletin-boards.posts.create', compact('organisations'));
    }

    public function store(Request $request, BulletinBoard $BulletinBoard)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'organisations' => 'nullable|array',
            'organisations.*' => 'exists:organisations,id',
            'status' => 'required|in:draft,published,cancelled',
            'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png,gif,mp4,avi,mov,doc,docx,xls,xlsx|max:20480', // 20MB max
            'thumbnails.*' => 'nullable|string'
        ]);

        // Création du bulletin board parent
        $bulletinBoard = BulletinBoard::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'user_id' => Auth::id(),
        ]);

        // Création du post
        $post = Post::create([
            'bulletin_board_id' => $bulletinBoard->id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'start_date' => $validated['start_date'] ?? now(),
            'end_date' => $validated['end_date'],
            'status' => $validated['status'],
            'user_id' => Auth::id()
        ]);

        // Gestion des organisations
        if (!empty($validated['organisations'])) {
            $bulletinBoard->organisations()->attach($validated['organisations'], [
                'user_id' => Auth::id()
            ]);
        }

        // Gestion des pièces jointes et vignettes
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $key => $file) {
                $path = $file->store('bulletin_board_attachments');

                $mimeType = $file->getMimeType();
                $fileType = explode('/', $mimeType)[0];

                $attachment = Attachment::create([
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'crypt' => md5_file($file),
                    'crypt_sha512' => hash_file('sha512', $file->getRealPath()),
                    'mime_type' => $mimeType,
                    'size' => $file->getSize(),
                    'creator_id' => Auth::id(),
//                    'type' => 'board'
                ]);

                // Gestion de la vignette
                if ($request->has('thumbnails') && isset($request->thumbnails[$key])) {
                    $thumbnailData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->thumbnails[$key]));
                    $thumbnailPath = 'thumbnails_bulletin_board/' . $attachment->id . '.jpg';
                    $stored = Storage::disk('public')->put($thumbnailPath, $thumbnailData);

                    if ($stored) {
                        $attachment->update(['thumbnail_path' => $thumbnailPath]);
                    }
                } else {
                    // Génération automatique de vignette pour les images et vidéos
                    if (in_array($fileType, ['image', 'video'])) {
                        $thumbnailPath = $this->generateThumbnail($file, $attachment->id, $fileType);
                        if ($thumbnailPath) {
                            $attachment->update(['thumbnail_path' => $thumbnailPath]);
                        }
                    }
                }

                $bulletinBoard->attachments()->attach($attachment->id, ['user_id' => Auth::id()]);
            }
        }

        return redirect()
            ->route('bulletin-boards.posts.show', $post)
            ->with('success', 'Publication créée avec succès.');
    }

    public function show( BulletinBoard $BulletinBoard, Post $post)
    {
        $post->load(['bulletinBoard.organisations', 'bulletinBoard.attachments', 'user']);
        return view('bulletin-boards.posts.show', compact('post'));
    }

    public function edit(BulletinBoard $BulletinBoard, Post $post)
    {
        $this->authorize('update', $post->bulletinBoard);

        $organisations = Organisation::all();
        return view('bulletin-boards.posts.edit', compact('post', 'organisations'));
    }

    public function update(BulletinBoard $BulletinBoard, Request $request, Post $post)
    {
        $this->authorize('update', $post->bulletinBoard);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'organisations' => 'nullable|array',
            'organisations.*' => 'exists:organisations,id',
            'status' => 'required|in:draft,published,cancelled',
            'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png,gif,mp4,avi,mov,doc,docx,xls,xlsx|max:20480',
            'thumbnails.*' => 'nullable|string'
        ]);

        // Mise à jour du bulletin board parent
        $post->bulletinBoard->update([
            'name' => $validated['name'],
            'description' => $validated['description']
        ]);

        // Mise à jour du post
        $post->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status']
        ]);

        // Mise à jour des organisations
        if (isset($validated['organisations'])) {
            $post->bulletinBoard->organisations()->sync($validated['organisations'], [
                'user_id' => Auth::id()
            ]);
        }

        // Gestion des nouvelles pièces jointes et vignettes
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $key => $file) {
                $path = $file->store('bulletin_board_attachments');

                $mimeType = $file->getMimeType();
                $fileType = explode('/', $mimeType)[0];

                $attachment = Attachment::create([
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'crypt' => md5_file($file),
                    'crypt_sha512' => hash_file('sha512', $file->getRealPath()),
                    'mime_type' => $mimeType,
                    'size' => $file->getSize(),
                    'creator_id' => Auth::id(),
                    'type' => 'bulletin_board'
                ]);

                // Gestion de la vignette
                if ($request->has('thumbnails') && isset($request->thumbnails[$key])) {
                    $thumbnailData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->thumbnails[$key]));
                    $thumbnailPath = 'thumbnails_bulletin_board/' . $attachment->id . '.jpg';
                    $stored = Storage::disk('public')->put($thumbnailPath, $thumbnailData);

                    if ($stored) {
                        $attachment->update(['thumbnail_path' => $thumbnailPath]);
                    }
                } else {
                    // Génération automatique de vignette pour les images et vidéos
                    if (in_array($fileType, ['image', 'video'])) {
                        $thumbnailPath = $this->generateThumbnail($file, $attachment->id, $fileType);
                        if ($thumbnailPath) {
                            $attachment->update(['thumbnail_path' => $thumbnailPath]);
                        }
                    }
                }

                $post->bulletinBoard->attachments()->attach($attachment->id, ['user_id' => Auth::id()]);
            }
        }

        return redirect()
            ->route('bulletin-boards.posts.show', $post)
            ->with('success', 'Publication mise à jour avec succès.');
    }

    public function destroy(BulletinBoard $BulletinBoard, Post $post)
    {
        $this->authorize('delete', $post->bulletinBoard);

        // Suppression du bulletin board parent (va supprimer le post via la cascade)
        $post->bulletinBoard->delete();

        return redirect()
            ->route('bulletin-boards.posts.index')
            ->with('success', 'Publication supprimée avec succès.');
    }

    public function toggleStatus(BulletinBoard $BulletinBoard, Post $post)
    {
        $this->authorize('update', $post->bulletinBoard);

        $post->update([
            'status' => $post->status === 'published' ? 'draft' : 'published'
        ]);

        return back()->with('success', 'Statut de la publication mis à jour avec succès.');
    }

    public function cancel(BulletinBoard $BulletinBoard, Post $post)
    {
        $this->authorize('update', $post->bulletinBoard);

        $post->update(['status' => 'cancelled']);

        return back()->with('success', 'Publication annulée avec succès.');
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
                $frame = $video->frame(TimeCode::fromSeconds(1));
                $frame->save(storage_path('app/public/' . $thumbnailPath));
            } else {
                return null;
            }

            return $thumbnailPath;
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la génération de la vignette: ' . $e->getMessage());
            return null;
        }
    }

    public function getThumbnailUrl($attachment)
    {
        if ($attachment->thumbnail_path) {
            return Storage::url($attachment->thumbnail_path);
        }

        // Icônes par défaut selon le type de fichier
        $extension = pathinfo($attachment->name, PATHINFO_EXTENSION);
        switch(strtolower($extension)) {
            case 'pdf':
                return asset('icons/pdf.png');
            case 'doc':
            case 'docx':
                return asset('icons/word.png');
            case 'xls':
            case 'xlsx':
                return asset('icons/excel.png');
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
                return Storage::url($attachment->path);
            case 'mp4':
            case 'avi':
            case 'mov':
                return asset('icons/video.png');
            default:
                return asset('icons/file.png');
        }
    }
}
