<?php

namespace App\Http\Controllers;

use App\Models\BulletinBoard;
use App\Models\Post;
use App\Models\Attachment;
use App\Models\Organisation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Intervention\Image\Facades\Image;

class PostController extends Controller
{
    /**
     * Affiche la liste des publications d'un tableau d'affichage
     */
    public function index(BulletinBoard $bulletinBoard)
    {
        $posts = Post::with(['bulletinBoard', 'creator', 'attachments'])
            ->where('bulletin_board_id', $bulletinBoard->id)
            ->when(request('search'), function($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when(request('status'), function($query, $status) {
                return $query->where('status', $status);
            })
            ->when(request('organisation'), function($query, $organisationId) {
                return $query->whereHas('bulletinBoard.organisations', function($q) use ($organisationId) {
                    $q->where('organisations.id', $organisationId);
                });
            })
            ->latest()
            ->paginate(10);

        $organisations = Organisation::all();

        return view('bulletin-boards.posts.index', compact('bulletinBoard', 'posts', 'organisations'));
    }

    /**
     * Affiche le formulaire de création d'une publication
     */
    public function create(BulletinBoard $bulletinBoard)
    {
        return view('bulletin-boards.posts.create', compact('bulletinBoard'));
    }

    /**
     * Enregistre une nouvelle publication
     */
    public function store(Request $request, BulletinBoard $bulletinBoard)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|string|in:draft,published,cancelled',
        ]);

        $post = Post::create([
            'bulletin_board_id' => $bulletinBoard->id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'start_date' => $validated['start_date'] ?? now(),
            'end_date' => $validated['end_date'],
            'status' => $validated['status'],
            'created_by' => Auth::id()
        ]);

        // Redirection vers la page d'ajout de pièces jointes si "add_attachments" est coché
        if ($request->has('add_attachments')) {
            return redirect()->route('bulletin-boards.posts.attachments.create', [$bulletinBoard, $post])
                ->with('success', 'Publication créée avec succès. Vous pouvez maintenant ajouter des pièces jointes.');
        }

        return redirect()->route('bulletin-boards.posts.show', [$bulletinBoard, $post])
            ->with('success', 'Publication créée avec succès.');
    }

    /**
     * Affiche une publication spécifique
     */
    public function show(BulletinBoard $bulletinBoard, Post $post)
    {
        if ($post->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        $post->load(['bulletinBoard', 'attachments', 'creator']);

        return view('bulletin-boards.posts.show', compact('bulletinBoard', 'post'));
    }

    /**
     * Affiche le formulaire d'édition d'une publication
     */
    public function edit(BulletinBoard $bulletinBoard, Post $post)
    {
        if ($post->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        return view('bulletin-boards.posts.edit', compact('bulletinBoard', 'post'));
    }

    /**
     * Met à jour une publication existante
     */
    public function update(Request $request, BulletinBoard $bulletinBoard, Post $post)
    {
        if ($post->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        $this->authorize('update', $post);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|string|in:draft,published,cancelled',
        ]);

        $post->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status']
        ]);

        // Redirection vers la page d'ajout de pièces jointes si "add_attachments" est coché
        if ($request->has('add_attachments')) {
            return redirect()->route('bulletin-boards.posts.attachments.create', [$bulletinBoard, $post])
                ->with('success', 'Publication mise à jour avec succès. Vous pouvez maintenant ajouter des pièces jointes.');
        }

        return redirect()->route('bulletin-boards.posts.show', [$bulletinBoard, $post])
            ->with('success', 'Publication mise à jour avec succès.');
    }

    /**
     * Supprime une publication
     */
    public function destroy(BulletinBoard $bulletinBoard, Post $post)
    {
        if ($post->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        $this->authorize('delete', $post);

        // Suppression des pièces jointes physiques
        foreach ($post->attachments as $attachment) {
            Storage::delete($attachment->path);
            if ($attachment->thumbnail_path) {
                Storage::disk('public')->delete($attachment->thumbnail_path);
            }
        }

        // Détachement des pièces jointes
        $post->attachments()->detach();

        // Suppression de la publication
        $post->delete();

        return redirect()->route('bulletin-boards.show', $bulletinBoard)
            ->with('success', 'Publication supprimée avec succès.');
    }

    /**
     * Change le statut d'une publication (publié/brouillon)
     */
    public function toggleStatus(BulletinBoard $bulletinBoard, Post $post)
    {
        if ($post->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        $this->authorize('update', $post);

        $post->update([
            'status' => $post->status === 'published' ? 'draft' : 'published'
        ]);

        return back()->with('success', 'Statut de la publication mis à jour avec succès.');
    }

    /**
     * Annule une publication
     */
    public function cancel(BulletinBoard $bulletinBoard, Post $post)
    {
        if ($post->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        $this->authorize('update', $post);

        $post->update(['status' => 'cancelled']);

        return back()->with('success', 'Publication annulée avec succès.');
    }

    /*
     * GESTION DES PIÈCES JOINTES
     */

    /**
     * Affiche la liste des pièces jointes d'une publication
     */
    public function attachmentsIndex(BulletinBoard $bulletinBoard, Post $post)
    {
        if ($post->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        $attachments = $post->attachments;

        return view('bulletin-boards.posts.attachments.index', compact('bulletinBoard', 'post', 'attachments'));
    }

    /**
     * Affiche le formulaire d'ajout de pièce jointe
     */
    public function attachmentsCreate(BulletinBoard $bulletinBoard, Post $post)
    {
        if ($post->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        return view('bulletin-boards.posts.attachments.create', compact('bulletinBoard', 'post'));
    }

    /**
     * Enregistre une nouvelle pièce jointe
     */
    public function attachmentsStore(Request $request, BulletinBoard $bulletinBoard, Post $post)
    {
        if ($post->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        try {
            $request->validate([
                'name' => 'required|max:100',
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png,gif,mp4,avi,mov,doc,docx,xls,xlsx|max:20480', // 20MB max
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
                'creator_id' => Auth::id(),
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
                // Génération automatique de vignette pour images
                if ($fileType === 'image') {
                    $thumbnailPath = $this->generateThumbnail($file, $attachment->id);
                    if ($thumbnailPath) {
                        $attachment->thumbnail_path = $thumbnailPath;
                        $attachment->save();
                    }
                }
            }

            $post->attachments()->attach($attachment->id, ['created_by' => Auth::id()]);

            return redirect()->route('bulletin-boards.posts.show', [$bulletinBoard, $post])
                ->with('success', 'Pièce jointe ajoutée avec succès.');
        } catch (Exception $e) {
            Log::error('Erreur lors de l\'ajout de pièce jointe: ' . $e->getMessage());
            return redirect()->route('bulletin-boards.posts.show', [$bulletinBoard, $post])
                ->with('error', 'Une erreur est survenue lors de l\'ajout de la pièce jointe.');
        }
    }

    /**
     * Génère une vignette pour une image
     */
    private function generateThumbnail($file, $attachmentId)
    {
        $thumbnailPath = 'thumbnails_post/' . $attachmentId . '.jpg';

        try {
            $img = Image::make($file->getRealPath());
            $img->fit(300, 300);
            $img->save(storage_path('app/public/' . $thumbnailPath));
            return $thumbnailPath;
        } catch (Exception $e) {
            Log::error('Erreur lors de la génération de la vignette: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Affiche une pièce jointe spécifique
     */
    public function attachmentsShow(BulletinBoard $bulletinBoard, Post $post, Attachment $attachment)
    {
        if ($post->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        // Vérifier que la pièce jointe est bien liée à cette publication
        if (!$post->attachments->contains($attachment)) {
            return abort(404);
        }

        return view('bulletin-boards.posts.attachments.show', compact('bulletinBoard', 'post', 'attachment'));
    }

    /**
     * Supprime une pièce jointe
     */
    public function attachmentsDestroy(BulletinBoard $bulletinBoard, Post $post, Attachment $attachment)
    {
        if ($post->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        // Vérifier que la pièce jointe est bien liée à cette publication
        if (!$post->attachments->contains($attachment)) {
            return abort(404);
        }

        // Vérifier les autorisations
        $this->authorize('delete', $attachment);

        // Détacher la pièce jointe de la publication
        $post->attachments()->detach($attachment->id);

        // Supprimer le fichier physique
        Storage::delete($attachment->path);

        // Supprimer la vignette si elle existe
        if ($attachment->thumbnail_path) {
            Storage::disk('public')->delete($attachment->thumbnail_path);
        }

        // Supprimer l'enregistrement
        $attachment->delete();

        return redirect()->route('bulletin-boards.posts.show', [$bulletinBoard, $post])
            ->with('success', 'Pièce jointe supprimée avec succès.');
    }

    /**
     * Prévisualise une pièce jointe
     */
    public function attachmentsPreview(Attachment $attachment)
    {
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
     * Télécharge une pièce jointe
     */
    public function attachmentsDownload(Attachment $attachment)
    {
        $filePath = storage_path('app/' . $attachment->path);

        if (!File::exists($filePath)) {
            abort(404);
        }

        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        $fileName = $attachment->name . '.' . $fileExtension;

        return response()->download($filePath, $fileName);
    }

    /**
     * Retourne la liste des pièces jointes pour les requêtes AJAX
     */
    public function attachmentsList(BulletinBoard $bulletinBoard, Post $post)
    {
        if ($post->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        $attachments = $post->attachments;

        if (request()->ajax()) {
            return response()->view('bulletin-boards.posts.attachments.partials.list',
                compact('bulletinBoard', 'post', 'attachments'));
        }

        return redirect()->route('bulletin-boards.posts.attachments.index', [$bulletinBoard, $post]);
    }

    /**
     * Enregistre des pièces jointes via AJAX
     */
    public function attachmentsAjaxStore(Request $request, BulletinBoard $bulletinBoard, Post $post)
    {
        if ($post->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        $request->validate([
            'files.*' => 'required|file|mimes:pdf,jpg,jpeg,png,gif,mp4,avi,mov,doc,docx,xls,xlsx|max:20480',
            'name' => 'nullable|string|max:100',
        ]);

        $results = [];

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('post_attachments');

                $mimeType = $file->getMimeType();
                $fileType = explode('/', $mimeType)[0];

                $name = $request->input('name') ?: $file->getClientOriginalName();

                $attachment = Attachment::create([
                    'path' => $path,
                    'name' => $name,
                    'crypt' => md5_file($file),
                    'crypt_sha512' => hash_file('sha512', $file->getRealPath()),
                    'size' => $file->getSize(),
                    'creator_id' => Auth::id(),
                    'mime_type' => $mimeType,
                    'type' => 'post',
                ]);

                // Génération de vignette si nécessaire
                if ($fileType === 'image') {
                    $thumbnailPath = $this->generateThumbnail($file, $attachment->id);
                    if ($thumbnailPath) {
                        $attachment->thumbnail_path = $thumbnailPath;
                        $attachment->save();
                    }
                }

                $post->attachments()->attach($attachment->id, ['created_by' => Auth::id()]);

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
     * Supprime une pièce jointe via AJAX
     */
    public function attachmentsAjaxDestroy(BulletinBoard $bulletinBoard, Post $post, Attachment $attachment)
    {
        if ($post->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        // Vérifier que la pièce jointe est bien liée à cette publication
        if (!$post->attachments->contains($attachment)) {
            return response()->json([
                'success' => false,
                'message' => 'Pièce jointe non trouvée'
            ], 404);
        }

        // Vérifier les autorisations
        if (!Auth::user()->can('delete', $attachment) && !Auth::user()->can('update', $post)) {
            return response()->json([
                'success' => false,
                'message' => 'Action non autorisée'
            ], 403);
        }

        // Détacher la pièce jointe de la publication
        $post->attachments()->detach($attachment->id);

        // Supprimer le fichier physique
        Storage::delete($attachment->path);

        // Supprimer la vignette si elle existe
        if ($attachment->thumbnail_path) {
            Storage::disk('public')->delete($attachment->thumbnail_path);
        }

        // Supprimer l'enregistrement
        $attachment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pièce jointe supprimée avec succès'
        ]);
    }
}
