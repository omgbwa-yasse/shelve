<?php

namespace App\Http\Controllers;

use App\Models\BulletinBoard;
use App\Models\BulletinBoardComment;
use Illuminate\Http\Request;

class BulletinBoardCommentController extends Controller
{
    public function store(Request $request, BulletinBoard $bulletinBoard)
    {
        if (!setting('bulletin_board.allow_comments', true)) {
            return back()->with('error', 'Les commentaires sont désactivés.');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $comment = $bulletinBoard->comments()->create([
            'content' => $validated['content'],
            'user_id' => auth()->id(),
            'status' => setting('bulletin_board.moderation_required', false) ? 'pending' : 'approved'
        ]);

        return back()->with('success',
            setting('bulletin_board.moderation_required', false)
                ? 'Commentaire soumis et en attente de modération.'
                : 'Commentaire publié avec succès.'
        );
    }

    public function destroy(BulletinBoardComment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return back()->with('success', 'Commentaire supprimé avec succès.');
    }

    public function approve(BulletinBoardComment $comment)
    {
        $this->authorize('moderate', $comment);

        $comment->update(['status' => 'approved']);

        return back()->with('success', 'Commentaire approuvé avec succès.');
    }

    public function reject(BulletinBoardComment $comment)
    {
        $this->authorize('moderate', $comment);

        $comment->update(['status' => 'rejected']);

        return back()->with('success', 'Commentaire rejeté.');
    }
}
