<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\RecordBookLoan;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    /**
     * Display a listing of loans.
     */
    public function index(Request $request)
    {
        $query = RecordBookLoan::with(['copy.book', 'borrower']);

        // Filter by status
        $status = $request->get('status', 'active');

        if ($status === 'active') {
            $query->whereNull('return_date');
        } elseif ($status === 'overdue') {
            $query->whereNull('return_date')
                  ->where('due_date', '<', now());
        } elseif ($status === 'returned') {
            $query->whereNotNull('return_date');
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('borrower', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('copy.book', function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        $loans = $query->orderBy('loan_date', 'desc')->paginate(20);

        // Statistics
        $statistics = [
            'active' => RecordBookLoan::whereNull('return_date')->count(),
            'overdue' => RecordBookLoan::whereNull('return_date')->where('due_date', '<', now())->count(),
            'today_returns' => RecordBookLoan::whereDate('due_date', today())->whereNull('return_date')->count(),
            'month_loans' => RecordBookLoan::whereMonth('loan_date', now()->month)->count(),
        ];

        return view('library.loans.index', compact('loans', 'statistics', 'status'));
    }

    /**
     * Show the form for creating a new loan.
     */
    public function create()
    {
        return view('library.loans.create');
    }

    /**
     * Store a newly created loan in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'copy_id' => 'required|exists:record_book_copies,id',
            'borrower_id' => 'required|exists:users,id',
            'loan_date' => 'required|date',
            'due_date' => 'required|date|after:loan_date',
            'notes' => 'nullable|string',
        ]);

        $validated['librarian_id'] = auth()->id();
        $validated['status'] = 'active';

        $loan = RecordBookLoan::create($validated);

        return redirect()->route('library.loans.show', $loan->id)
            ->with('success', 'Prêt créé avec succès.');
    }

    /**
     * Display the specified loan.
     */
    public function show($id)
    {
        $loan = RecordBookLoan::with(['copy.book', 'borrower', 'librarian'])
            ->findOrFail($id);

        return view('library.loans.show', compact('loan'));
    }

    /**
     * Process loan return.
     */
    public function return(Request $request, $id)
    {
        $loan = RecordBookLoan::findOrFail($id);

        $loan->update([
            'return_date' => now(),
            'status' => 'returned',
        ]);

        return redirect()->route('library.loans.index')
            ->with('success', 'Retour enregistré avec succès.');
    }

    /**
     * Display overdue loans.
     */
    public function overdue()
    {
        $loans = RecordBookLoan::with(['copy.book', 'borrower'])
            ->whereNull('return_date')
            ->where('due_date', '<', now())
            ->orderBy('due_date')
            ->paginate(20);

        return view('library.loans.overdue', compact('loans'));
    }

    /**
     * Display loans history.
     */
    public function history()
    {
        $loans = RecordBookLoan::with(['copy.book', 'borrower'])
            ->whereNotNull('return_date')
            ->orderBy('return_date', 'desc')
            ->paginate(20);

        return view('library.loans.history', compact('loans'));
    }
}
