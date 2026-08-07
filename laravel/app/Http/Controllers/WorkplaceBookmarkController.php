<?php

namespace App\Http\Controllers;

use App\Models\Workplace;
use App\Models\WorkplaceBookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkplaceBookmarkController extends Controller
{
    public function index(Workplace $workplace)
    {
        $this->authorize('view', $workplace);

        $bookmarks = $workplace->bookmarks()
            ->where('user_id', Auth::id())
            ->with('bookmarkable')
            ->latest()
            ->get();

        return view('workplaces.bookmarks.index', compact('workplace', 'bookmarks'));
    }

    public function store(Request $request, Workplace $workplace)
    {
        $this->authorize('view', $workplace);

        $validated = $request->validate([
            'bookmarkable_type' => 'required|string',
            'bookmarkable_id' => 'required|integer',
            'note' => 'nullable|string',
        ]);

        // Toggle bookmark
        $bookmark = WorkplaceBookmark::where('workplace_id', $workplace->id)
            ->where('user_id', Auth::id())
            ->where('bookmarkable_type', $validated['bookmarkable_type'])
            ->where('bookmarkable_id', $validated['bookmarkable_id'])
            ->first();

        if ($bookmark) {
            $bookmark->delete();
            return back()->with('success', 'Favori retiré');
        } else {
            WorkplaceBookmark::create([
                'workplace_id' => $workplace->id,
                'user_id' => Auth::id(),
                'bookmarkable_type' => $validated['bookmarkable_type'],
                'bookmarkable_id' => $validated['bookmarkable_id'],
                'note' => $validated['note'] ?? null,
            ]);
            return back()->with('success', 'Favori ajouté');
        }
    }

    public function destroy(Workplace $workplace, WorkplaceBookmark $bookmark)
    {
        $this->authorize('view', $workplace);

        if ($bookmark->user_id !== Auth::id()) {
            abort(403);
        }

        $bookmark->delete();
        return back()->with('success', 'Favori supprimé');
    }
}
