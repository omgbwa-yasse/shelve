<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RecordBookLoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ReaderController extends Controller
{
    /**
     * Display a listing of readers.
     */
    public function index(Request $request)
    {
        $query = User::with(['organisation', 'roles'])
            ->where('current_organisation_id', auth()->user()->current_organisation_id);

        // Recherche par nom, prénom ou email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtrer par rôle (lecteurs uniquement)
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $readers = $query->orderBy('name')
            ->paginate(20);

        return view('library.readers.index', compact('readers'));
    }

    /**
     * Show the form for creating a new reader.
     */
    public function create()
    {
        return view('library.readers.create');
    }

    /**
     * Store a newly created reader in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'birthday' => 'nullable|date',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['current_organisation_id'] = auth()->user()->current_organisation_id;

        $user = User::create($validated);

        // Assigner le rôle de lecteur
        $user->assignRole('reader');

        return redirect()->route('library.readers.index')
            ->with('success', 'Lecteur créé avec succès.');
    }

    /**
     * Display the specified reader.
     */
    public function show($id)
    {
        $reader = User::with(['organisation', 'roles'])
            ->findOrFail($id);

        // Statistiques des prêts
        $activeLoans = RecordBookLoan::with('copy.book')
            ->where('borrower_id', $id)
            ->whereNull('return_date')
            ->get();

        $loanHistory = RecordBookLoan::with('copy.book')
            ->where('borrower_id', $id)
            ->whereNotNull('return_date')
            ->orderBy('loan_date', 'desc')
            ->limit(10)
            ->get();

        $stats = [
            'total_loans' => RecordBookLoan::where('borrower_id', $id)->count(),
            'active_loans' => $activeLoans->count(),
            'overdue_loans' => RecordBookLoan::where('borrower_id', $id)
                ->whereNull('return_date')
                ->where('due_date', '<', now())
                ->count(),
            'total_late_fees' => RecordBookLoan::where('borrower_id', $id)
                ->sum('late_fee'),
        ];

        return view('library.readers.show', compact('reader', 'activeLoans', 'loanHistory', 'stats'));
    }

    /**
     * Show the form for editing the specified reader.
     */
    public function edit($id)
    {
        $reader = User::findOrFail($id);
        return view('library.readers.edit', compact('reader'));
    }

    /**
     * Update the specified reader in storage.
     */
    public function update(Request $request, $id)
    {
        $reader = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'birthday' => 'nullable|date',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $reader->update($validated);

        return redirect()->route('library.readers.index')
            ->with('success', 'Lecteur modifié avec succès.');
    }

    /**
     * Remove the specified reader from storage.
     */
    public function destroy($id)
    {
        $reader = User::findOrFail($id);

        // Vérifier qu'il n'a pas de prêts actifs
        $activeLoans = RecordBookLoan::where('borrower_id', $id)
            ->whereNull('return_date')
            ->count();

        if ($activeLoans > 0) {
            return redirect()->route('library.readers.index')
                ->with('error', 'Impossible de supprimer un lecteur avec des prêts actifs.');
        }

        $reader->delete();

        return redirect()->route('library.readers.index')
            ->with('success', 'Lecteur supprimé avec succès.');
    }

    /**
     * Generate reader card.
     */
    public function card($id)
    {
        $reader = User::with('organisation')->findOrFail($id);

        $activeLoans = RecordBookLoan::where('borrower_id', $id)
            ->whereNull('return_date')
            ->count();

        return view('library.readers.card', compact('reader', 'activeLoans'));
    }
}
