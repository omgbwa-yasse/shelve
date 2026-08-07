<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Record;
use App\Models\RecordComment;
use App\Models\RecordShare;
use App\Models\RecordShortcut;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * Collaboration sur les notices (étape 8) : partage ad hoc, favoris, commentaires
 * et raccourcis.
 */
class RecordCollaborationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Partage
    |--------------------------------------------------------------------------
    */
    public function shareForm(Record $record)
    {
        Gate::authorize('records_update');

        return view('records.shares.form', [
            'record' => $record,
            'users' => User::orderBy('name')->get(),
            'roles' => Role::orderBy('name')->get(),
            'shares' => $record->shares()->with(['user', 'role', 'creator'])->get(),
        ]);
    }

    public function share(Request $request, Record $record)
    {
        Gate::authorize('records_update');

        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'role_id' => 'nullable|exists:roles,id',
            'permission' => 'required|in:view,edit',
            'expires_at' => 'nullable|date|after:now',
        ]);

        if (! $request->filled('user_id') && ! $request->filled('role_id')) {
            return back()->withErrors(['user_id' => 'Choisissez un utilisateur ou un rôle (groupe).']);
        }

        RecordShare::create([
            'record_id' => $record->id,
            'user_id' => $request->input('user_id'),
            'role_id' => $request->input('role_id'),
            'permission' => $request->input('permission'),
            'expires_at' => $request->input('expires_at'),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('records.shares', $record)->with('success', 'Notice partagée.');
    }

    public function revokeShare(Record $record, RecordShare $share)
    {
        Gate::authorize('records_update');

        abort_if($share->record_id !== $record->id, 404);

        $share->delete();

        return redirect()->route('records.shares', $record)->with('success', 'Partage révoqué.');
    }

    /*
    |--------------------------------------------------------------------------
    | Favoris
    |--------------------------------------------------------------------------
    */
    public function toggleFavorite(Request $request, Record $record)
    {
        Gate::authorize('records_view');

        $existing = Favorite::forUser(Auth::id())
            ->where('favoriteable_type', Record::class)
            ->where('favoriteable_id', $record->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return response()->json(['favorite' => false]);
        }

        Favorite::create([
            'user_id' => Auth::id(),
            'favoriteable_type' => Record::class,
            'favoriteable_id' => $record->id,
            'shared' => $request->boolean('shared'),
        ]);

        return response()->json(['favorite' => true]);
    }

    /*
    |--------------------------------------------------------------------------
    | Commentaires
    |--------------------------------------------------------------------------
    */
    public function storeComment(Request $request, Record $record)
    {
        Gate::authorize('records_view');

        $request->validate(['content' => 'required|string|max:5000']);

        RecordComment::create([
            'record_id' => $record->id,
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
        ]);

        return back()->with('success', 'Commentaire ajouté.');
    }

    public function updateComment(Request $request, Record $record, RecordComment $comment)
    {
        Gate::authorize('records_view');

        abort_if($comment->record_id !== $record->id, 404);
        abort_unless($comment->isOwnedBy(), 403);

        $request->validate(['content' => 'required|string|max:5000']);

        $comment->update([
            'content' => $request->input('content'),
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'Commentaire mis à jour.');
    }

    public function destroyComment(Record $record, RecordComment $comment)
    {
        Gate::authorize('records_view');

        abort_if($comment->record_id !== $record->id, 404);
        abort_unless($comment->isOwnedBy(), 403);

        $comment->delete();

        return back()->with('success', 'Commentaire supprimé.');
    }

    /*
    |--------------------------------------------------------------------------
    | Raccourcis
    |--------------------------------------------------------------------------
    */
    public function storeShortcut(Request $request, Record $record)
    {
        Gate::authorize('records_view');

        $request->validate(['label' => 'nullable|string|max:255']);

        RecordShortcut::create([
            'record_id' => $record->id,
            'user_id' => Auth::id(),
            'label' => $request->input('label'),
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'Raccourci créé.');
    }

    public function destroyShortcut(Record $record, RecordShortcut $shortcut)
    {
        Gate::authorize('records_view');

        abort_if($shortcut->record_id !== $record->id, 404);

        $shortcut->delete();

        return back()->with('success', 'Raccourci supprimé.');
    }
}
