<?php

namespace App\Http\Controllers;

use App\Models\TaskComment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskCommentController extends Controller
{
    /**
     * Constructeur avec middleware d'authentification
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Affiche tous les commentaires d'une tâche.
     */
    public function index(Task $task)
    {
        $this->authorize('view', $task);

        $comments = $task->comments()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('tasks.comments.index', compact('task', 'comments'));
    }

    /**
     * Enregistre un nouveau commentaire pour une tâche.
     */
    public function store(Request $request, Task $task)
    {
        $this->authorize('comment', $task);

        $validated = $request->validate([
            'content' => 'required|string',
            'type' => 'nullable|string',
        ]);

        $comment = new TaskComment();
        $comment->task_id = $task->id;
        $comment->user_id = Auth::id();
        $comment->content = $validated['content'];
        $comment->type = $validated['type'] ?? 'regular';
        $comment->save();

        return back()->with('success', 'Commentaire ajouté avec succès.');
    }

    /**
     * Met à jour un commentaire existant.
     */
    public function update(Request $request, Task $task, TaskComment $comment)
    {
        $this->authorize('update', $comment);

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $comment->content = $validated['content'];
        $comment->save();

        return back()->with('success', 'Commentaire mis à jour avec succès.');
    }

    /**
     * Supprime un commentaire.
     */
    public function destroy(Task $task, TaskComment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return back()->with('success', 'Commentaire supprimé avec succès.');
    }
}
