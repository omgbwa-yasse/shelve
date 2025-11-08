<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\RecordBook;
use App\Models\RecordBookLoan;
use App\Models\RecordAuthor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    /**
     * Display statistics dashboard.
     */
    public function index()
    {
        // Statistiques générales
        $stats = [
            'total_books' => RecordBook::count(),
            'total_copies' => RecordBook::sum('total_copies'),
            'available_copies' => RecordBook::sum('available_copies'),
            'total_authors' => RecordAuthor::count(),
            'total_loans' => RecordBookLoan::count(),
            'active_loans' => RecordBookLoan::whereNull('return_date')->count(),
            'overdue_loans' => RecordBookLoan::whereNull('return_date')
                ->where('due_date', '<', now())
                ->count(),
            'total_readers' => User::whereHas('roles', function ($q) {
                $q->where('name', 'reader');
            })->count(),
        ];

        // Prêts par mois (12 derniers mois)
        $loansByMonth = RecordBookLoan::selectRaw("
                DATE_FORMAT(loan_date, '%Y-%m') as month,
                COUNT(*) as count
            ")
            ->where('loan_date', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Top 10 livres les plus empruntés
        $topBooks = RecordBook::with(['authors', 'publisher'])
            ->orderBy('loan_count', 'desc')
            ->limit(10)
            ->get();

        // Top 10 auteurs les plus empruntés
        $topAuthors = RecordAuthor::withCount('books')
            ->orderBy('books_count', 'desc')
            ->limit(10)
            ->get();

        // Distribution par catégorie Dewey
        $booksByCategory = RecordBook::selectRaw("
                SUBSTRING(dewey, 1, 1) as category,
                COUNT(*) as count
            ")
            ->whereNotNull('dewey')
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        return view('library.statistics.index', compact(
            'stats',
            'loansByMonth',
            'topBooks',
            'topAuthors',
            'booksByCategory'
        ));
    }

    /**
     * Display loans statistics.
     */
    public function loans()
    {
        // Statistiques de prêts détaillées
        $stats = [
            'total_loans' => RecordBookLoan::count(),
            'active_loans' => RecordBookLoan::whereNull('return_date')->count(),
            'returned_loans' => RecordBookLoan::whereNotNull('return_date')->count(),
            'overdue_loans' => RecordBookLoan::whereNull('return_date')
                ->where('due_date', '<', now())
                ->count(),
            'avg_loan_duration' => RecordBookLoan::whereNotNull('return_date')
                ->selectRaw('AVG(DATEDIFF(return_date, loan_date)) as avg_days')
                ->value('avg_days'),
            'total_renewals' => RecordBookLoan::sum('renewal_count'),
            'total_late_fees' => RecordBookLoan::sum('late_fee'),
        ];

        // Prêts par jour de la semaine
        $loansByDayOfWeek = RecordBookLoan::selectRaw("
                DAYOFWEEK(loan_date) as day,
                DAYNAME(loan_date) as day_name,
                COUNT(*) as count
            ")
            ->where('loan_date', '>=', now()->subMonths(3))
            ->groupBy('day', 'day_name')
            ->orderBy('day')
            ->get();

        // Prêts par heure de la journée
        $loansByHour = RecordBookLoan::selectRaw("
                HOUR(created_at) as hour,
                COUNT(*) as count
            ")
            ->where('created_at', '>=', now()->subMonths(3))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Durée moyenne par catégorie
        $avgDurationByCategory = RecordBookLoan::join('record_book_copies', 'record_book_loans.copy_id', '=', 'record_book_copies.id')
            ->join('record_books', 'record_book_copies.book_id', '=', 'record_books.id')
            ->whereNotNull('record_book_loans.return_date')
            ->whereNotNull('record_books.dewey')
            ->selectRaw("
                SUBSTRING(record_books.dewey, 1, 1) as category,
                AVG(DATEDIFF(record_book_loans.return_date, record_book_loans.loan_date)) as avg_days,
                COUNT(*) as count
            ")
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        // Top emprunteurs
        $topBorrowers = User::withCount(['loans' => function ($q) {
                $q->where('loan_date', '>=', now()->subYear());
            }])
            ->having('loans_count', '>', 0)
            ->orderBy('loans_count', 'desc')
            ->limit(10)
            ->get();

        return view('library.statistics.loans', compact(
            'stats',
            'loansByDayOfWeek',
            'loansByHour',
            'avgDurationByCategory',
            'topBorrowers'
        ));
    }

    /**
     * Display categories statistics.
     */
    public function categories()
    {
        // Statistiques par catégorie Dewey
        $categoriesStats = RecordBook::selectRaw("
                SUBSTRING(dewey, 1, 1) as main_category,
                SUBSTRING(dewey, 1, 3) as sub_category,
                COUNT(*) as books_count,
                SUM(total_copies) as total_copies,
                SUM(available_copies) as available_copies,
                SUM(loan_count) as total_loans
            ")
            ->whereNotNull('dewey')
            ->groupBy('main_category', 'sub_category')
            ->orderBy('main_category')
            ->orderBy('sub_category')
            ->get();

        // Catégories principales avec noms
        $mainCategories = [
            '0' => 'Informatique, information & ouvrages généraux',
            '1' => 'Philosophie & psychologie',
            '2' => 'Religion',
            '3' => 'Sciences sociales',
            '4' => 'Langues',
            '5' => 'Sciences',
            '6' => 'Technologie',
            '7' => 'Arts & loisirs',
            '8' => 'Littérature',
            '9' => 'Histoire & géographie',
        ];

        // Regrouper par catégorie principale
        $categoryGroups = $categoriesStats->groupBy('main_category')
            ->map(function ($items, $category) use ($mainCategories) {
                return [
                    'name' => $mainCategories[$category] ?? 'Autre',
                    'books_count' => $items->sum('books_count'),
                    'total_copies' => $items->sum('total_copies'),
                    'available_copies' => $items->sum('available_copies'),
                    'total_loans' => $items->sum('total_loans'),
                    'subcategories' => $items,
                ];
            });

        return view('library.statistics.categories', compact('categoryGroups', 'mainCategories'));
    }
}
