<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\RecordBookLoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $request->validate([
            'borrower_identifier' => 'required|string',
            'copy_barcode' => 'required|string',
            'loan_date' => 'required|date',
            'due_date' => 'required|date|after:loan_date',
            'notes' => 'nullable|string',
        ]);

        // Find Borrower by ID (Inventory Number)
        $borrower = \App\Models\User::find($request->borrower_identifier);
        if (!$borrower) {
            return back()->withErrors(['borrower_identifier' => 'Lecteur non trouvé (ID invalide).'])->withInput();
        }

        // Find Copy by Barcode
        $copy = \App\Models\RecordBookCopy::where('barcode', $request->copy_barcode)->first();
        if (!$copy) {
            return back()->withErrors(['copy_barcode' => 'Exemplaire non trouvé (Code-barres invalide).'])->withInput();
        }

        // Check availability
        if (!$copy->is_available) {
            return back()->withErrors(['copy_barcode' => 'Cet exemplaire n\'est pas disponible (Statut: ' . $copy->status_label . ').'])->withInput();
        }

        $data = [
            'borrower_id' => $borrower->id,
            'copy_id' => $copy->id,
            'loan_date' => $request->loan_date,
            'due_date' => $request->due_date,
            'notes' => $request->notes,
            'librarian_id' => Auth::id(),
            'status' => 'active',
        ];

        $loan = RecordBookLoan::create($data);

        // Update copy status
        $copy->markAsOnLoan($loan);

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

    /**
     * Check borrower existence and return name.
     */
    public function checkBorrower(Request $request)
    {
        $identifier = $request->get('identifier');
        $borrower = \App\Models\User::find($identifier);

        if ($borrower) {
            return response()->json([
                'found' => true,
                'name' => $borrower->name . ' ' . $borrower->surname,
                'email' => $borrower->email
            ]);
        }

        return response()->json(['found' => false]);
    }

    /**
     * Check copy existence and return book title.
     */
    public function checkCopy(Request $request)
    {
        $barcode = $request->get('barcode');
        $copy = \App\Models\RecordBookCopy::where('barcode', $barcode)->with('book')->first();

        if ($copy) {
            return response()->json([
                'found' => true,
                'title' => $copy->book->title ?? 'Titre inconnu',
                'status' => $copy->status,
                'is_available' => $copy->is_available,
                'status_label' => $copy->status_label
            ]);
        }

        return response()->json(['found' => false]);
    }

    /**
     * Show the form for returning a book (by scanning).
     */
    public function returnForm()
    {
        return view('library.loans.return');
    }

    /**
     * Process loan return from the scanning form.
     */
    public function storeReturn(Request $request)
    {
        $request->validate([
            'copy_barcode' => 'required|string',
        ]);

        $copy = \App\Models\RecordBookCopy::where('barcode', $request->copy_barcode)->first();

        if (!$copy) {
            return back()->withErrors(['copy_barcode' => 'Exemplaire non trouvé.'])->withInput();
        }

        $loan = \App\Models\RecordBookLoan::where('copy_id', $copy->id)
            ->whereNull('return_date')
            ->first();

        if (!$loan) {
            return back()->withErrors(['copy_barcode' => 'Aucun prêt actif trouvé pour cet exemplaire.'])->withInput();
        }

        $loan->update([
            'return_date' => now(),
            'status' => 'returned',
        ]);

        $copy->markAsReturned();

        return redirect()->route('library.loans.return-form')
            ->with('success', 'Retour enregistré avec succès pour : ' . ($copy->book->title ?? 'Livre inconnu'));
    }

    /**
     * Check for active loan by barcode.
     */
    public function checkActiveLoan(Request $request)
    {
        $barcode = $request->get('barcode');
        $copy = \App\Models\RecordBookCopy::where('barcode', $barcode)->with('book')->first();

        if (!$copy) {
            return response()->json(['found' => false, 'message' => 'Exemplaire non trouvé']);
        }

        $loan = \App\Models\RecordBookLoan::where('copy_id', $copy->id)
            ->whereNull('return_date')
            ->with('borrower')
            ->first();

        if ($loan) {
            return response()->json([
                'found' => true,
                'title' => $copy->book->title ?? 'Titre inconnu',
                'borrower_name' => $loan->borrower->name . ' ' . $loan->borrower->surname,
                'loan_date' => $loan->loan_date->format('d/m/Y'),
                'due_date' => $loan->due_date->format('d/m/Y'),
                'is_overdue' => $loan->is_overdue,
                'days_overdue' => $loan->days_overdue
            ]);
        }

        return response()->json([
            'found' => false, 
            'message' => 'Cet exemplaire n\'est pas en prêt (Statut: ' . $copy->status_label . ')'
        ]);
    }
}
