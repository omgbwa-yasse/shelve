<?php

namespace App\Http\Controllers;

use App\Models\BulletinBoard;
use App\Models\Post;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index(BulletinBoard $bulletinBoard)
    {
        $posts = Post::with(['bulletinBoard', 'creator'])
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

        return view('bulletin-boards.posts.index', compact('posts','bulletinBoard', 'organisations'));
    }

    public function create(BulletinBoard $bulletinBoard)
    {
        $organisations = Organisation::all();
        return view('bulletin-boards.posts.create', compact('bulletinBoard','organisations'));
    }

    public function store(Request $request, BulletinBoard $bulletinBoard)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'organisations' => 'nullable|array',
            'organisations.*' => 'exists:organisations,id',
            'status' => 'required|in:draft,published,cancelled',
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
            return redirect()->route('posts.attachments.create', $post->id)
                ->with('success', 'Publication créée avec succès. Vous pouvez maintenant ajouter des pièces jointes.');
        }

        return redirect()
            ->route('bulletin-boards.posts.show', [$bulletinBoard, $post])
            ->with('success', 'Publication créée avec succès.');
    }

    public function show(BulletinBoard $bulletinBoard, Post $post)
    {
        $post->load(['bulletinBoard', 'attachments', 'creator']);

        return view('bulletin-boards.posts.show', compact('post', 'bulletinBoard'));
    }

    public function edit(BulletinBoard $bulletinBoard, Post $post)
    {
        $organisations = Organisation::all();
        return view('bulletin-boards.posts.edit', compact('post', 'organisations', 'bulletinBoard'));
    }

    public function update(BulletinBoard $bulletinBoard, Request $request, Post $post)
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

        // Redirection vers la page d'ajout de pièces jointes si "add_attachments" est coché
        if ($request->has('add_attachments')) {
            return redirect()->route('posts.attachments.create', $post->id)
                ->with('success', 'Publication mise à jour avec succès. Vous pouvez maintenant ajouter des pièces jointes.');
        }

        return redirect()
            ->route('bulletin-boards.posts.show', [$bulletinBoard, $post])
            ->with('success', 'Publication mise à jour avec succès.');
    }

    public function destroy(BulletinBoard $bulletinBoard, Post $post)
    {
        // La suppression des pièces jointes est maintenant déléguée au PostAttachmentController
        // On se contente de supprimer le post, les pièces jointes seront détachées automatiquement
        // grâce aux contraintes de clé étrangère
        $post->delete();

        return redirect()
            ->route('bulletin-boards.index')
            ->with('success', 'Publication supprimée avec succès.');
    }

    public function toggleStatus(BulletinBoard $bulletinBoard, Post $post)
    {
        $post->update([
            'status' => $post->status === 'published' ? 'draft' : 'published'
        ]);

        return back()->with('success', 'Statut de la publication mis à jour avec succès.');
    }

    public function cancel(BulletinBoard $bulletinBoard, Post $post)
    {
        $post->update(['status' => 'cancelled']);

        return back()->with('success', 'Publication annulée avec succès.');
    }


    /**
         * Retourne la liste partielle des pièces jointes pour les requêtes AJAX
         */
        public function getAttachmentsList($postId)
        {
            $post = Post::findOrFail($postId);
            $attachments = $post->attachments;

            if (request()->ajax()) {
                return response()->view('posts.attachments.partials.list', compact('post', 'attachments'));
            }

            return redirect()->route('posts.attachments.index', $postId);
        }

        /**
        * Gestion AJAX pour l'upload de fichiers
        */
        public function ajaxStore(Request $request, $postId)
        {
            $post = Post::findOrFail($postId);

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
                        'creator_id' => auth()->id(),
                        'mime_type' => $mimeType,
                        'type' => 'post',
                    ]);

                    // Générer une vignette si nécessaire
                    if (in_array($fileType, ['image', 'video'])) {
                        $thumbnailPath = $this->generateThumbnail($file, $attachment->id, $fileType);
                        if ($thumbnailPath) {
                            $attachment->thumbnail_path = $thumbnailPath;
                            $attachment->save();
                        }
                    }

                    $post->attachments()->attach($attachment->id, ['created_by' => auth()->id()]);

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
        public function ajaxDestroy($postId, $attachmentId)
        {
            $post = Post::findOrFail($postId);
            $attachment = Attachment::findOrFail($attachmentId);

            // Vérifier les autorisations
            if (!auth()->user()->can('delete', $attachment) && !auth()->user()->can('update', $post)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Action non autorisée'
                ], 403);
            }

            // Détacher la pièce jointe du post
            $post->attachments()->detach($attachment->id);

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
