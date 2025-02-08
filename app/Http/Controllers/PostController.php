<?php

namespace App\Http\Controllers;

use App\Models\BulletinBoard;
use App\Models\Post;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index()
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

    public function create()
    {
        $organisations = Organisation::all();
        return view('bulletin-boards.posts.create', compact('organisations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'organisations' => 'nullable|array',
            'organisations.*' => 'exists:organisations,id',
            'status' => 'required|in:draft,published,cancelled'
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

        // Gestion des pièces jointes
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('bulletin_board_attachments');
                $bulletinBoard->attachments()->attach(
                    Attachment::create([
                        'path' => $path,
                        'name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'creator_id' => Auth::id()
                    ])->id,
                    ['user_id' => Auth::id()]
                );
            }
        }

        return redirect()
            ->route('bulletin-boards.posts.show', $post)
            ->with('success', 'Publication créée avec succès.');
    }

    public function show(Post $post)
    {
        $post->load(['bulletinBoard.organisations', 'bulletinBoard.attachments', 'user']);
        return view('bulletin-boards.posts.show', compact('post'));
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post->bulletinBoard);

        $organisations = Organisation::all();
        return view('bulletin-boards.posts.edit', compact('post', 'organisations'));
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post->bulletinBoard);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'organisations' => 'nullable|array',
            'organisations.*' => 'exists:organisations,id',
            'status' => 'required|in:draft,published,cancelled'
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

        // Gestion des nouvelles pièces jointes
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('bulletin_board_attachments');
                $post->bulletinBoard->attachments()->attach(
                    Attachment::create([
                        'path' => $path,
                        'name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'creator_id' => Auth::id()
                    ])->id,
                    ['user_id' => Auth::id()]
                );
            }
        }

        return redirect()
            ->route('bulletin-boards.posts.show', $post)
            ->with('success', 'Publication mise à jour avec succès.');
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post->bulletinBoard);

        // Suppression du bulletin board parent (va supprimer le post via la cascade)
        $post->bulletinBoard->delete();

        return redirect()
            ->route('bulletin-boards.posts.index')
            ->with('success', 'Publication supprimée avec succès.');
    }

    public function toggleStatus(Post $post)
    {
        $this->authorize('update', $post->bulletinBoard);

        $post->update([
            'status' => $post->status === 'published' ? 'draft' : 'published'
        ]);

        return back()->with('success', 'Statut de la publication mis à jour avec succès.');
    }

    public function cancel(Post $post)
    {
        $this->authorize('update', $post->bulletinBoard);

        $post->update(['status' => 'cancelled']);

        return back()->with('success', 'Publication annulée avec succès.');
    }
}
