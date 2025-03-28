<?php

namespace App\Http\Controllers;

use App\Models\BulletinBoard;
use App\Models\Event;
use App\Models\Organisation;
use App\Http\Requests\BulletinBoardRequest;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BulletinBoardController extends Controller
{
    public function myPosts()
    {
        $posts = Post::where('user_id', auth()->id())
            ->with(['bulletinBoard', 'user'])
            ->latest()
            ->paginate(10);

        $stats = [
            'active_posts' => Post::where('user_id', auth()->id())
                ->where('status', 'published')
                ->count(),
            'upcoming_events' => Event::where('user_id', auth()->id())
                ->where('start_date', '>=', now())
                ->count(),
            'drafts' => Post::where('user_id', auth()->id())
                ->where('status', 'draft')
                ->count(),
            'total_comments' => Post::where('user_id', auth()->id())->count()
        ];

        return view('bulletin-boards.my-posts', compact('posts', 'stats'));
    }

    public function create()
    {
        $organisations = Organisation::all();
        return view('bulletin-boards.create', compact('organisations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:post,event'
        ]);

        $bulletinBoard = BulletinBoard::create([
            'name' => $request->name,
            'description' => $request->description,
            'user_id' => auth()->id(),
        ]);

        if ($request->type === 'event') {
            $event = Event::create([
                'bulletin_board_id' => $bulletinBoard->id,
                'name' => $request->name,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'location' => $request->location,
                'status' => $request->status ?? 'draft',
                'user_id' => auth()->id()
            ]);
        } else {
            $post = Post::create([
                'bulletin_board_id' => $bulletinBoard->id,
                'name' => $request->name,
                'description' => $request->description,
                'start_date' => now(),
                'status' => $request->status ?? 'draft',
                'user_id' => auth()->id()
            ]);
        }

        if ($request->has('organisations')) {
            $bulletinBoard->organisations()->attach($request->organisations, [
                'user_id' => auth()->id()
            ]);
        }

        return redirect()->route('bulletin-boards.show', $bulletinBoard)
            ->with('success', 'Publication créée avec succès.');
    }

    public function show(BulletinBoard $bulletinBoard)
    {
        $bulletinBoard->load(['user', 'organisations', 'events', 'posts']);
        return view('bulletin-boards.show', compact('bulletinBoard'));
    }
    public function update(Request $request, BulletinBoard $bulletinBoard)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:post,event,announcement',
            'start_date' => 'required_if:type,event|nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'organisations' => 'nullable|array',
            'organisations.*' => 'exists:organisations,id',
            'status' => 'required|in:draft,published'
        ]);

        $bulletinBoard->update($validated);

        if (isset($validated['organisations'])) {
            $bulletinBoard->organisations()->sync($validated['organisations']);
        }

        return redirect()
            ->route('bulletin-boards.show', $bulletinBoard)
            ->with('success', 'Publication mise à jour avec succès.');
    }




    public function toggleArchive(BulletinBoard $bulletinBoard)
    {
        if ($bulletinBoard->trashed()) {
            $bulletinBoard->restore();
            $message = 'Publication restaurée avec succès.';
        } else {
            $bulletinBoard->delete();
            $message = 'Publication archivée avec succès.';
        }

        return back()->with('success', $message);
    }








    public function index(Request $request)
    {
        $bulletinBoards = BulletinBoard::with(['user', 'organisations'])
            ->latest()
            ->paginate(10);
        $organisations = Organisation::all();
        return view('bulletin-boards.index', compact('bulletinBoards', 'organisations'));
    }








    public function archives(Request $request)
    {
        $query = BulletinBoard::onlyTrashed()->where('user_id', Auth::id());

        // Apply filters if provided
        if ($request->filled('type')) {
            switch ($request->type) {
                case 'event':
                    $query = Event::onlyTrashed()->where('user_id', Auth::id());
                    break;
                case 'post':
                    $query = Post::onlyTrashed()->where('user_id', Auth::id());
                    break;
                default:
                    $query = BulletinBoard::onlyTrashed()->where('user_id', Auth::id());
                    break;
            }
        }

        if ($request->filled('archived_date')) {
            $query->whereDate('deleted_at', $request->archived_date);
        }

        if ($request->filled('organisation') && $request->type === null) {
            $query->whereHas('organisations', function ($q) use ($request) {
                $q->where('id', $request->organisation);
            });
        }

        // Apply sorting if provided
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'date_asc':
                    $query->orderBy('deleted_at', 'asc');
                    break;
                case 'date_desc':
                    $query->orderBy('deleted_at', 'desc');
                    break;
                case 'name':
                    $query->orderBy('name', 'asc');
                    break;
            }
        }

        $archivedPosts = $query->paginate(20);
        $organisations = Organisation::all();

        return view('bulletin-boards.archives', compact('archivedPosts', 'organisations'));
    }








    public function destroy(BulletinBoard $bulletinBoard)
    {
        try {
            $bulletinBoard->delete();
            return redirect()
                ->route('bulletin-boards.index')
                ->with('success', 'Tableau d\'affichage supprimé avec succès.');

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Erreur lors de la suppression du tableau d\'affichage.');
        }
    }




    public function addAdministrator(Request $request, BulletinBoard $bulletinBoard)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'role' => 'required|in:admin,moderator'
            ]);

            if ($bulletinBoard->administrators->contains($validated['user_id'])) {
                return response()->json([
                    'message' => 'Cet utilisateur est déjà administrateur.'
                ], 422);
            }

            $bulletinBoard->administrators()->attach($validated['user_id'], [
                'role' => $validated['role'],
                'assigned_by_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'message' => 'Administrateur ajouté avec succès.',
                'user_id' => $validated['user_id'],
                'role' => $validated['role']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'ajout de l\'administrateur.'
            ], 500);
        }
    }


    public function removeAdministrator(Request $request, BulletinBoard $bulletinBoard)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id'
            ]);

            $adminRole = $bulletinBoard->administrators()
                ->where('user_id', $validated['user_id'])
                ->first()
                ->pivot
                ->role;

            if ($adminRole === 'super_admin') {
                return response()->json([
                    'message' => 'Impossible de retirer un super administrateur.'
                ], 422);
            }

            $bulletinBoard->administrators()->detach($validated['user_id']);

            return response()->json([
                'message' => 'Administrateur retiré avec succès.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du retrait de l\'administrateur.'
            ], 500);
        }
    }


    public function addOrganisation(Request $request, BulletinBoard $bulletinBoard)
    {
        try {
            $validated = $request->validate([
                'organisation_id' => 'required|exists:organisations,id'
            ]);

            if ($bulletinBoard->organisations->contains($validated['organisation_id'])) {
                return response()->json([
                    'message' => 'Cette organisation est déjà associée.'
                ], 422);
            }

            $bulletinBoard->organisations()->attach($validated['organisation_id'], [
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'message' => 'Organisation ajoutée avec succès.',
                'organisation_id' => $validated['organisation_id']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'ajout de l\'organisation.'
            ], 500);
        }
    }


    public function removeOrganisation(Request $request, BulletinBoard $bulletinBoard)
    {
        try {
            $validated = $request->validate([
                'organisation_id' => 'required|exists:organisations,id'
            ]);

            $bulletinBoard->organisations()->detach($validated['organisation_id']);

            return response()->json([
                'message' => 'Organisation retirée avec succès.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du retrait de l\'organisation.'
            ], 500);
        }
    }

    public function dashboard()
    {
        $stats = [
            'total_posts' => BulletinBoard::count(),
            'active_events' => BulletinBoard::where('type', 'event')
                ->where('start_date', '>=', now())
                ->count(),
            'my_posts' => BulletinBoard::where('user_id', auth()->id())->count(),
            'recent_activities' => BulletinBoard::with('user')
                ->latest()
                ->take(5)
                ->get()
        ];

        $upcomingEvents = BulletinBoard::where('type', 'event')
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->take(5)
            ->get();

        return view('bulletin-boards.dashboard', compact('stats', 'upcomingEvents'));
    }





    protected function handleAttachments(BulletinBoard $bulletinBoard, $attachments)
    {
        if (!$attachments) {
            return;
        }

        foreach ($attachments as $attachment) {
            $path = $attachment->store('bulletin-board-attachments', 'public');
            $bulletinBoard->attachments()->create([
                'name' => $attachment->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $attachment->getMimeType(),
                'size' => $attachment->getSize(),
                'user_id' => auth()->id()
            ]);
        }
    }
    private function checkManagementPermissions(BulletinBoard $bulletinBoard)
    {
        $userRole = $bulletinBoard->administrators()
            ->where('user_id', Auth::id())
            ->first()
            ->pivot
            ->role ?? null;

        if (!in_array($userRole, ['super_admin', 'admin'])) {
            throw new \Illuminate\Auth\Access\AuthorizationException(
                'Vous n\'avez pas les permissions nécessaires pour effectuer cette action.'
            );
        }

        return true;
    }
}

