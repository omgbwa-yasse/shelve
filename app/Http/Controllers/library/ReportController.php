<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\RecordBook;
use App\Models\RecordBookLoan;
use App\Models\RecordAuthor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    /**
     * Display reports dashboard.
     */
    public function index()
    {
        $availableReports = [
            [
                'name' => 'Rapport de collection',
                'description' => 'Liste complète des livres par catégorie',
                'route' => 'library.reports.collection',
            ],
            [
                'name' => 'Rapport de prêts',
                'description' => 'Historique des prêts avec statistiques',
                'route' => 'library.reports.loans',
            ],
            [
                'name' => 'Rapport d\'inventaire',
                'description' => 'État des stocks et exemplaires',
                'route' => 'library.reports.inventory',
            ],
            [
                'name' => 'Rapport lecteurs',
                'description' => 'Liste des lecteurs actifs',
                'route' => 'library.reports.readers',
            ],
            [
                'name' => 'Rapport retards',
                'description' => 'Prêts en retard avec amendes',
                'route' => 'library.reports.overdue',
            ],
        ];

        return view('library.reports.index', compact('availableReports'));
    }

    /**
     * Generate collection report.
     */
    public function collection(Request $request)
    {
        $format = $request->get('format', 'html'); // html, csv, pdf

        $books = RecordBook::with(['authors', 'publisher'])
            ->orderBy('dewey')
            ->orderBy('title')
            ->get();

        $stats = [
            'total_books' => $books->count(),
            'total_copies' => $books->sum('total_copies'),
            'by_category' => $books->groupBy(function ($book) {
                return substr($book->dewey, 0, 1);
            }),
        ];

        if ($format === 'csv') {
            return $this->exportCollectionCsv($books);
        }

        return view('library.reports.collection', compact('books', 'stats'));
    }

    /**
     * Generate loans report.
     */
    public function loans(Request $request)
    {
        $query = RecordBookLoan::with(['copy.book.authors', 'borrower']);

        // Filtres de date
        if ($request->filled('start_date')) {
            $query->where('loan_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('loan_date', '<=', $request->end_date);
        }

        $loans = $query->orderBy('loan_date', 'desc')->get();

        $stats = [
            'total_loans' => $loans->count(),
            'returned' => $loans->where('return_date', '!=', null)->count(),
            'active' => $loans->whereNull('return_date')->count(),
            'total_late_fees' => $loans->sum('late_fee'),
        ];

        return view('library.reports.loans', compact('loans', 'stats'));
    }

    /**
     * Generate inventory report.
     */
    public function inventory()
    {
        $books = RecordBook::with(['copies'])
            ->orderBy('title')
            ->get();

        $stats = [
            'total_titles' => $books->count(),
            'total_copies' => $books->sum('total_copies'),
            'available' => $books->sum('available_copies'),
            'on_loan' => $books->sum('total_copies') - $books->sum('available_copies'),
            'missing' => RecordBook::whereRaw('total_copies != available_copies')->count(),
        ];

        return view('library.reports.inventory', compact('books', 'stats'));
    }

    /**
     * Generate readers report.
     */
    public function readers()
    {
        $readers = \App\Models\User::with(['roles'])
            ->whereHas('roles', function ($q) {
                $q->where('name', 'reader');
            })
            ->withCount([
                'loans as active_loans_count' => function ($q) {
                    $q->whereNull('return_date');
                },
                'loans as total_loans_count',
            ])
            ->orderBy('name')
            ->get();

        $stats = [
            'total_readers' => $readers->count(),
            'active_readers' => $readers->where('active_loans_count', '>', 0)->count(),
        ];

        return view('library.reports.readers', compact('readers', 'stats'));
    }

    /**
     * Generate overdue report.
     */
    public function overdue()
    {
        $overdueLoans = RecordBookLoan::with(['copy.book.authors', 'borrower'])
            ->whereNull('return_date')
            ->where('due_date', '<', now())
            ->orderBy('due_date')
            ->get();

        // Calculer les amendes
        $overdueLoans->each(function ($loan) {
            $daysOverdue = now()->diffInDays($loan->due_date);
            $loan->days_overdue = $daysOverdue;
            $loan->calculated_fee = $daysOverdue * 0.50; // 0.50 par jour
        });

        $stats = [
            'total_overdue' => $overdueLoans->count(),
            'total_fees' => $overdueLoans->sum('calculated_fee'),
            'avg_days_overdue' => $overdueLoans->avg('days_overdue'),
        ];

        return view('library.reports.overdue', compact('overdueLoans', 'stats'));
    }

    /**
     * Export collection to CSV.
     */
    protected function exportCollectionCsv($books)
    {
        $filename = 'collection_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($books) {
            $file = fopen('php://output', 'w');

            // En-têtes CSV
            fputcsv($file, ['ISBN', 'Titre', 'Auteurs', 'Éditeur', 'Année', 'Dewey', 'Exemplaires', 'Disponibles']);

            // Données
            foreach ($books as $book) {
                fputcsv($file, [
                    $book->isbn,
                    $book->title,
                    $book->authors->pluck('full_name')->implode(', '),
                    $book->publisher->name ?? '',
                    $book->publication_year,
                    $book->dewey,
                    $book->total_copies,
                    $book->available_copies,
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
