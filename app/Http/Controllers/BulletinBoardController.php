<?php

namespace App\Http\Controllers;

use App\Models\BulletinBoard;
use App\Models\Organisation;
use App\Http\Requests\BulletinBoardRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BulletinBoardController extends Controller
{
    public function index(Request $request)
    {
        $bulletinBoards = BulletinBoard::with(['user', 'organisations'])
            ->latest()
            ->paginate(10);
        $organisations = Organisation::all();
        return view('bulletin-boards.index', compact('bulletinBoards', 'organisations'));
    }


    public function create()
    {
        return view('bulletin-boards.create');
    }


    public function store(Request $request)
    {
        try {
            $bulletinBoard = BulletinBoard::create([
                'name' => $request->name,
                'description' => $request->description,
                'user_id' => Auth::id(),
            ]);

            if ($request->has('organisations')) {
                $bulletinBoard->organisations()->attach($request->organisations);
            }

            // Ajouter le créateur comme super admin
            $bulletinBoard->administrators()->attach(Auth::id(), [
                'role' => 'super_admin',
                'assigned_by_id' => Auth::id()
            ]);

            return redirect()
                ->route('bulletin-boards.show', $bulletinBoard)
                ->with('success', 'Tableau d\'affichage créé avec succès.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création du tableau d\'affichage.');
        }
    }



    public function show(BulletinBoard $bulletinBoard)
    {
        $bulletinBoard->load([
            'user',
            'organisations',
            'administrators',
            'events' => function ($query) {
                $query->latest()->take(5);
            },
            'attachments' => function ($query) {
                $query->latest()->take(5);
            }
        ]);

        return view('bulletin-boards.show', compact('bulletinBoard'));
    }



    public function edit(BulletinBoard $bulletinBoard)
    {
        return view('bulletin-boards.edit', compact('bulletinBoard'));
    }



    public function update(Request $request, BulletinBoard $bulletinBoard)
    {
        try {
            $bulletinBoard->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            if ($request->has('organisations')) {
                $bulletinBoard->organisations()->sync($request->organisations);
            }

            return redirect()
                ->route('bulletin-boards.show', $bulletinBoard)
                ->with('success', 'Tableau d\'affichage mis à jour avec succès.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour du tableau d\'affichage.');
        }
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
