<?php

namespace Database\Seeders;

use App\Models\RecordBook;
use App\Models\RecordBookCopy;
use App\Models\RecordBookLoan;
use App\Models\RecordBookReservation;
use App\Models\User;
use App\Services\RecordBookService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seeder pour migrer les exemplaires, prêts et réservations
 * Crée des données de test pour le système de circulation
 */
class MigrateCopiesSeeder extends Seeder
{
    private RecordBookService $bookService;

    public function __construct()
    {
        $this->bookService = new RecordBookService();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info("🚀 Création d'exemplaires de test...");

        // Récupérer quelques livres existants
        $books = RecordBook::with(['authors', 'publisher'])->take(4)->get();

        if ($books->isEmpty()) {
            $this->command->warn("⚠️  Aucun livre trouvé. Exécutez d'abord MigrateBooksSeeder.");
            return;
        }

        // Récupérer quelques utilisateurs pour les prêts
        $users = User::take(5)->get();

        if ($users->isEmpty()) {
            $this->command->warn("⚠️  Aucun utilisateur trouvé. Création impossible de prêts.");
            $users = collect();
        }

        $admin = User::where('email', 'admin@example.com')->first() ?? $users->first();

        $copiesCreated = 0;
        $loansCreated = 0;
        $reservationsCreated = 0;

        // Créer des exemplaires pour chaque livre
        foreach ($books as $index => $book) {
            $this->command->info("📚 Livre: {$book->title}");

            // Créer 3 à 5 exemplaires par livre
            $numCopies = rand(3, 5);

            for ($i = 1; $i <= $numCopies; $i++) {
                $status = 'available';
                $isOnLoan = false;

                // Varier les statuts
                if ($i === 1 && $users->isNotEmpty()) {
                    $status = 'on_loan';
                    $isOnLoan = true;
                } elseif ($i === 2 && $index === 0) {
                    $status = 'reserved';
                } elseif ($i === $numCopies && $index === 1) {
                    $status = 'in_repair';
                }

                $copy = $this->bookService->createCopy($book, [
                    'barcode' => "BOOK-{$book->id}-" . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'call_number' => $this->generateCallNumber($book, $index),
                    'location' => $this->getRandomLocation(),
                    'shelf' => "R-" . rand(1, 20),
                    'status' => $status,
                    'condition' => $this->getRandomCondition(),
                    'acquisition_date' => Carbon::now()->subMonths(rand(1, 24)),
                    'acquisition_price' => rand(10, 50) + (rand(0, 99) / 100),
                    'acquisition_source' => $this->getRandomSource(),
                    'notes' => $i === 1 ? "Exemplaire de référence" : null,
                ]);

                $copiesCreated++;

                $this->command->info("  ✓ Exemplaire créé: {$copy->barcode} [{$copy->status}]");
            }
        }

        // Créer quelques prêts actifs
        if ($users->isNotEmpty()) {
            $this->command->info("\n📖 Création de prêts de test...");

            $availableCopies = RecordBookCopy::where('status', 'on_loan')->take(3)->get();

            foreach ($availableCopies as $copy) {
                try {
                    // Remettre disponible temporairement pour tester la fonction loan
                    $copy->update(['status' => 'available', 'is_on_loan' => false]);

                    $borrower = $users->random();
                    $loan = $this->bookService->loanBook(
                        $copy,
                        $borrower,
                        14, // 14 jours
                        $admin
                    );

                    $loansCreated++;
                    $this->command->info("  ✓ Prêt créé: {$copy->barcode} → {$borrower->name} (jusqu'au {$loan->due_date->format('d/m/Y')})");
                } catch (\Exception $e) {
                    $this->command->error("  ✗ Erreur prêt: " . $e->getMessage());
                }
            }

            // Créer un prêt en retard
            $this->command->info("\n⏰ Création d'un prêt en retard...");
            $overdueCopy = RecordBookCopy::where('status', 'available')->first();

            if ($overdueCopy) {
                try {
                    $overdueLoan = $this->bookService->loanBook(
                        $overdueCopy,
                        $users->random(),
                        14,
                        $admin
                    );

                    // Modifier manuellement la date pour simuler un retard
                    $overdueLoan->update([
                        'loan_date' => Carbon::now()->subDays(20),
                        'due_date' => Carbon::now()->subDays(6),
                    ]);

                    $loansCreated++;
                    $this->command->info("  ✓ Prêt en retard créé: {$overdueCopy->barcode} (retard de 6 jours)");
                } catch (\Exception $e) {
                    $this->command->error("  ✗ Erreur: " . $e->getMessage());
                }
            }
        }

        // Créer quelques réservations
        if ($users->isNotEmpty()) {
            $this->command->info("\n📅 Création de réservations...");

            $popularBooks = RecordBook::whereHas('copies', function ($query) {
                $query->where('status', 'on_loan');
            })->take(2)->get();

            foreach ($popularBooks as $book) {
                // Créer 2-3 réservations par livre
                $numReservations = rand(2, 3);

                for ($i = 0; $i < $numReservations; $i++) {
                    try {
                        $user = $users->random();
                        $priority = $i === 0 ? 'high' : 'normal';

                        $reservation = $this->bookService->reserveBook(
                            $book,
                            $user,
                            $priority,
                            $i === 0 // Premier est VIP
                        );

                        $reservationsCreated++;
                        $this->command->info("  ✓ Réservation créée: {$book->title} → {$user->name} (position {$reservation->queue_position})");
                    } catch (\Exception $e) {
                        // Ignorer les doublons
                        if (!str_contains($e->getMessage(), 'déjà une réservation')) {
                            $this->command->error("  ✗ Erreur: " . $e->getMessage());
                        }
                    }
                }
            }
        }

        $this->command->newLine();
        $this->command->info("✅ Migration terminée:");
        $this->command->table(
            ['Type', 'Nombre'],
            [
                ['Exemplaires créés', $copiesCreated],
                ['Prêts créés', $loansCreated],
                ['Réservations créées', $reservationsCreated],
            ]
        );
    }

    /**
     * Générer une cote bibliothécaire (classification Dewey simplifiée)
     */
    private function generateCallNumber(RecordBook $book, int $index): string
    {
        // Catégories Dewey simplifiées
        $categories = [
            '000' => 'Informatique',
            '100' => 'Philosophie',
            '200' => 'Religion',
            '300' => 'Sciences sociales',
            '400' => 'Langues',
            '500' => 'Sciences',
            '600' => 'Technologie',
            '700' => 'Arts',
            '800' => 'Littérature',
            '900' => 'Histoire',
        ];

        $dewey = array_keys($categories)[$index % count($categories)];
        $subcategory = rand(10, 99);

        // Ajouter les 3 premières lettres de l'auteur
        $authorCode = '';
        if ($book->authors->isNotEmpty()) {
            $authorName = $book->authors->first()->name;
            $authorCode = strtoupper(substr(str_replace(' ', '', $authorName), 0, 3));
        }

        return "{$dewey}.{$subcategory} {$authorCode}";
    }

    /**
     * Obtenir un emplacement aléatoire
     */
    private function getRandomLocation(): string
    {
        $locations = [
            'Bibliothèque Centrale - Salle de lecture',
            'Bibliothèque Centrale - Réserve',
            'Annexe Nord - 1er étage',
            'Annexe Sud - Rez-de-chaussée',
            'Salle de référence',
            'Magasin principal',
        ];

        return $locations[array_rand($locations)];
    }

    /**
     * Obtenir une section aléatoire
     */
    private function getRandomSection(): string
    {
        $sections = [
            'Sciences et Technologies',
            'Littérature française',
            'Littérature étrangère',
            'Histoire et Géographie',
            'Arts et Culture',
            'Sciences humaines',
            'Jeunesse',
            'Bandes dessinées',
        ];

        return $sections[array_rand($sections)];
    }

    /**
     * Obtenir une condition aléatoire
     */
    private function getRandomCondition(): string
    {
        $conditions = ['excellent', 'good', 'good', 'good', 'fair', 'fair', 'poor'];
        return $conditions[array_rand($conditions)];
    }

    /**
     * Obtenir une source d'acquisition aléatoire
     */
    private function getRandomSource(): string
    {
        $sources = [
            'Librairie Dupont',
            'Amazon France',
            'Donation M. Martin',
            'Échange avec Université Paris',
            'Dépôt légal',
            'Achat direct éditeur',
        ];

        return $sources[array_rand($sources)];
    }

    /**
     * Obtenir un type d'acquisition aléatoire
     */
    private function getRandomAcquisitionType(): string
    {
        $types = ['purchase', 'purchase', 'purchase', 'donation', 'gift', 'exchange'];
        return $types[array_rand($types)];
    }
}
