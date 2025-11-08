<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\RecordPeriodicService;
use App\Models\RecordPeriodic;
use App\Models\RecordPeriodicIssue;
use App\Models\RecordPeriodicArticle;
use App\Models\RecordPeriodicSubscription;
use App\Models\User;
use App\Models\Organisation;
use Carbon\Carbon;

/**
 * Seeder pour les périodiques (Phase 8)
 *
 * Créer des exemples réalistes de:
 * - Revues scientifiques (Nature, Science, Cell)
 * - Revues médicales (The Lancet, JAMA, NEJM)
 * - Revues techniques (IEEE, ACM)
 * - Revues de sciences humaines
 */
class MigratePeriodicalsSeeder extends Seeder
{
    private RecordPeriodicService $service;
    private ?User $creator = null;
    private ?Organisation $organisation = null;

    public function run(): void
    {
        echo "\n🔄 Nettoyage des données existantes...\n";
        RecordPeriodicArticle::truncate();
        RecordPeriodicIssue::truncate();
        RecordPeriodicSubscription::truncate();
        RecordPeriodic::truncate();

        // Créer un utilisateur et une organisation si nécessaire
        $this->creator = User::first();
        if (!$this->creator) {
            $this->creator = User::create([
                'name' => 'Admin Periodicals',
                'surname' => 'System',
                'email' => 'admin.periodicals@shelve.local',
                'password' => bcrypt('password'),
                'birthday' => '1990-01-01',
            ]);
        }

        $this->organisation = Organisation::first();
        if (!$this->organisation) {
            $this->organisation = Organisation::create([
                'name' => 'Bibliothèque Universitaire',
                'code' => 'BU',
            ]);
        }

        $this->service = new RecordPeriodicService();

        echo "📚 Création des périodiques...\n";

        $this->createScientificJournals();
        $this->createMedicalJournals();
        $this->createTechnicalJournals();
        $this->createHumanitiesJournals();

        $stats = $this->service->getStatistics();

        echo "\n✅ Seed terminé!\n";
        echo "   📰 Total périodiques: {$stats['total_periodics']}\n";
        echo "   ✅ Actifs: {$stats['active_periodics']}\n";
        echo "   📊 Abonnements actifs: {$stats['active_subscriptions']}\n";
        echo "   📑 Total numéros: {$stats['total_issues']}\n";
        echo "   📄 Total articles: {$stats['total_articles']}\n";
        echo "   🔬 Articles peer-reviewed: {$stats['peer_reviewed_articles']}\n";
    }

    /**
     * Créer des revues scientifiques de prestige
     */
    private function createScientificJournals(): void
    {
        echo "  🔬 Revues scientifiques...\n";

        // Nature
        $nature = $this->service->createPeriodic([
            'title' => 'Nature',
            'issn' => '0028-0836',
            'eissn' => '1476-4687',
            'type' => 'scientific',
            'subject_area' => 'Multidisciplinary Sciences',
            'keywords' => ['science', 'research', 'biology', 'physics', 'chemistry'],
            'publisher' => 'Nature Publishing Group',
            'publisher_location' => 'London, UK',
            'language' => 'en',
            'frequency' => 'weekly',
            'first_year' => 1869,
            'website' => 'https://www.nature.com',
            'description' => 'Leading international weekly journal of science',
        ], $this->creator, $this->organisation);

        $this->createIssuesForPeriodic($nature, 12);
        $this->createSubscription($nature, 'active', 4500, '2025-01-01', '2025-12-31');

        // Science
        $science = $this->service->createPeriodic([
            'title' => 'Science',
            'issn' => '0036-8075',
            'eissn' => '1095-9203',
            'type' => 'scientific',
            'subject_area' => 'Multidisciplinary Sciences',
            'keywords' => ['science', 'research', 'innovation'],
            'publisher' => 'American Association for the Advancement of Science',
            'publisher_location' => 'Washington, DC, USA',
            'language' => 'en',
            'frequency' => 'weekly',
            'first_year' => 1880,
            'website' => 'https://www.science.org',
            'description' => 'Premier global science weekly',
        ], $this->creator, $this->organisation);

        $this->createIssuesForPeriodic($science, 10);
        $this->createSubscription($science, 'active', 4200, '2025-01-01', '2025-12-31');

        // Cell
        $cell = $this->service->createPeriodic([
            'title' => 'Cell',
            'issn' => '0092-8674',
            'eissn' => '1097-4172',
            'type' => 'scientific',
            'subject_area' => 'Cell Biology',
            'keywords' => ['cell biology', 'molecular biology', 'genetics'],
            'publisher' => 'Cell Press',
            'publisher_location' => 'Cambridge, MA, USA',
            'language' => 'en',
            'frequency' => 'biweekly',
            'first_year' => 1974,
            'website' => 'https://www.cell.com',
            'description' => 'Leading journal in experimental biology',
        ], $this->creator, $this->organisation);

        $this->createIssuesForPeriodic($cell, 8);
        $this->createSubscription($cell, 'active', 3800, '2025-01-01', '2025-12-31');
    }

    /**
     * Créer des revues médicales
     */
    private function createMedicalJournals(): void
    {
        echo "  🏥 Revues médicales...\n";

        // The Lancet
        $lancet = $this->service->createPeriodic([
            'title' => 'The Lancet',
            'issn' => '0140-6736',
            'eissn' => '1474-547X',
            'type' => 'scientific',
            'subject_area' => 'Medicine',
            'keywords' => ['medicine', 'health', 'clinical research'],
            'publisher' => 'Elsevier',
            'publisher_location' => 'London, UK',
            'language' => 'en',
            'frequency' => 'weekly',
            'first_year' => 1823,
            'website' => 'https://www.thelancet.com',
            'description' => 'One of the oldest and most prestigious medical journals',
        ], $this->creator, $this->organisation);

        $this->createIssuesForPeriodic($lancet, 10);
        $this->createSubscription($lancet, 'active', 3200, '2025-01-01', '2025-12-31');

        // JAMA
        $jama = $this->service->createPeriodic([
            'title' => 'JAMA',
            'subtitle' => 'The Journal of the American Medical Association',
            'issn' => '0098-7484',
            'eissn' => '1538-3598',
            'type' => 'scientific',
            'subject_area' => 'Medicine',
            'keywords' => ['medicine', 'healthcare', 'public health'],
            'publisher' => 'American Medical Association',
            'publisher_location' => 'Chicago, IL, USA',
            'language' => 'en',
            'frequency' => 'weekly',
            'first_year' => 1883,
            'website' => 'https://jamanetwork.com',
            'description' => 'Leading peer-reviewed medical journal',
        ], $this->creator, $this->organisation);

        $this->createIssuesForPeriodic($jama, 8);
        $this->createSubscription($jama, 'expiring', 2900, '2024-03-01', '2025-02-28');

        // NEJM
        $nejm = $this->service->createPeriodic([
            'title' => 'New England Journal of Medicine',
            'issn' => '0028-4793',
            'eissn' => '1533-4406',
            'type' => 'scientific',
            'subject_area' => 'Medicine',
            'keywords' => ['medicine', 'clinical trials', 'medical research'],
            'publisher' => 'Massachusetts Medical Society',
            'publisher_location' => 'Boston, MA, USA',
            'language' => 'en',
            'frequency' => 'weekly',
            'first_year' => 1812,
            'website' => 'https://www.nejm.org',
            'description' => 'Oldest continuously published medical journal',
        ], $this->creator, $this->organisation);

        $this->createIssuesForPeriodic($nejm, 6);
        $this->createSubscription($nejm, 'active', 3500, '2025-01-01', '2025-12-31');
    }

    /**
     * Créer des revues techniques
     */
    private function createTechnicalJournals(): void
    {
        echo "  💻 Revues techniques...\n";

        // IEEE Transactions
        $ieeeTransactions = $this->service->createPeriodic([
            'title' => 'IEEE Transactions on Software Engineering',
            'issn' => '0098-5589',
            'eissn' => '1939-3520',
            'type' => 'scientific',
            'subject_area' => 'Computer Science',
            'keywords' => ['software engineering', 'computer science', 'programming'],
            'publisher' => 'IEEE Computer Society',
            'publisher_location' => 'Los Alamitos, CA, USA',
            'language' => 'en',
            'frequency' => 'monthly',
            'first_year' => 1975,
            'website' => 'https://www.computer.org/csdl/journal/ts',
            'description' => 'Leading journal in software engineering research',
        ], $this->creator, $this->organisation);

        $this->createIssuesForPeriodic($ieeeTransactions, 6);
        $this->createSubscription($ieeeTransactions, 'active', 1800, '2025-01-01', '2025-12-31');

        // ACM Computing Surveys
        $acmSurveys = $this->service->createPeriodic([
            'title' => 'ACM Computing Surveys',
            'issn' => '0360-0300',
            'eissn' => '1557-7341',
            'type' => 'scientific',
            'subject_area' => 'Computer Science',
            'keywords' => ['computer science', 'surveys', 'reviews'],
            'publisher' => 'Association for Computing Machinery',
            'publisher_location' => 'New York, NY, USA',
            'language' => 'en',
            'frequency' => 'quarterly',
            'first_year' => 1969,
            'website' => 'https://dl.acm.org/journal/csur',
            'description' => 'Survey and tutorial articles in computing',
        ], $this->creator, $this->organisation);

        $this->createIssuesForPeriodic($acmSurveys, 4);
        $this->createSubscription($acmSurveys, 'active', 1500, '2025-01-01', '2025-12-31');
    }

    /**
     * Créer des revues de sciences humaines
     */
    private function createHumanitiesJournals(): void
    {
        echo "  📖 Revues sciences humaines...\n";

        // American Historical Review
        $ahr = $this->service->createPeriodic([
            'title' => 'The American Historical Review',
            'issn' => '0002-8762',
            'eissn' => '1937-5239',
            'type' => 'academic',
            'subject_area' => 'History',
            'keywords' => ['history', 'historiography', 'historical research'],
            'publisher' => 'Oxford University Press',
            'publisher_location' => 'Oxford, UK',
            'language' => 'en',
            'frequency' => 'quarterly',
            'first_year' => 1895,
            'website' => 'https://academic.oup.com/ahr',
            'description' => 'Flagship journal of the American Historical Association',
        ], $this->creator, $this->organisation);

        $this->createIssuesForPeriodic($ahr, 4);
        $this->createSubscription($ahr, 'active', 950, '2025-01-01', '2025-12-31');

        // Revue historique (ceased)
        $revueHistorique = $this->service->createPeriodic([
            'title' => 'Revue Historique',
            'issn' => '0035-3264',
            'type' => 'academic',
            'subject_area' => 'History',
            'keywords' => ['histoire', 'recherche historique'],
            'publisher' => 'Presses Universitaires de France',
            'publisher_location' => 'Paris, France',
            'language' => 'fr',
            'frequency' => 'quarterly',
            'first_year' => 1876,
            'last_year' => 2020,
            'is_active' => false,
            'status' => 'ceased',
            'description' => 'Une des plus anciennes revues historiques françaises',
        ], $this->creator, $this->organisation);

        $this->createIssuesForPeriodic($revueHistorique, 3, false);
        $this->createSubscription($revueHistorique, 'expired', 600, '2019-01-01', '2020-12-31');
    }

    /**
     * Créer des numéros pour un périodique
     */
    private function createIssuesForPeriodic(
        RecordPeriodic $periodic,
        int $count,
        bool $recent = true
    ): void {
        $startYear = $recent ? 2024 : 2019;

        for ($i = 1; $i <= $count; $i++) {
            $year = $startYear + floor($i / 12);
            $month = ($i % 12) + 1;
            $publicationDate = Carbon::create($year, $month, 1);

            // Calculer le statut
            $status = 'catalogued';
            if ($publicationDate->isFuture()) {
                $status = 'expected';
            } elseif ($i % 8 === 0) {
                $status = 'missing'; // 1 sur 8 est manquant
            }

            $issue = $this->service->addIssue($periodic, [
                'issue_number' => (string) $i,
                'volume' => (string) ceil($i / 12),
                'year' => (string) $year,
                'publication_date' => $publicationDate->format('Y-m-d'),
                'status' => $status,
                'received_date' => $status === 'catalogued' ? $publicationDate->addDays(7)->format('Y-m-d') : null,
                'location' => $status === 'catalogued' ? 'Rayon périodiques' : null,
                'call_number' => $status === 'catalogued' ? "{$periodic->code}/{$year}/{$i}" : null,
            ]);

            // Créer des articles pour les numéros catalogués
            if ($status === 'catalogued') {
                $this->createArticlesForIssue($issue, $periodic, rand(8, 12));
            }
        }
    }

    /**
     * Créer des articles pour un numéro
     */
    private function createArticlesForIssue(
        RecordPeriodicIssue $issue,
        RecordPeriodic $periodic,
        int $count
    ): void {
        $articleTypes = ['research', 'review', 'letter', 'editorial', 'case-study'];
        $sections = ['Articles', 'Reviews', 'Letters', 'Brief Communications', 'Perspective'];

        for ($i = 1; $i <= $count; $i++) {
            $articleType = $articleTypes[array_rand($articleTypes)];
            $isPeerReviewed = in_array($articleType, ['research', 'review', 'case-study']);

            $authors = $this->generateAuthors(rand(2, 5));

            // Generate unique DOI using periodic ID, issue ID, and article number
            $doi = "10.1000/{$periodic->code}.{$issue->year}.{$issue->issue_number}.{$i}";

            $this->service->addArticle($issue, [
                'title' => $this->generateArticleTitle($periodic->subject_area),
                'abstract' => $this->generateAbstract($periodic->subject_area),
                'authors' => $authors,
                'page_start' => ($i - 1) * 15 + 1,
                'page_end' => $i * 15,
                'section' => $sections[array_rand($sections)],
                'doi' => $doi,
                'keywords' => $periodic->keywords ?? [],
                'language' => $periodic->language,
                'article_type' => $articleType,
                'is_peer_reviewed' => $isPeerReviewed,
            ]);
        }
    }    /**
     * Créer un abonnement
     */
    private function createSubscription(
        RecordPeriodic $periodic,
        string $status,
        float $cost,
        string $startDate,
        string $endDate
    ): void {
        $statusValue = match ($status) {
            'expiring' => Carbon::parse($endDate)->diffInDays(Carbon::now()) < 30 ? 'active' : 'active',
            default => $status,
        };

        $this->service->createSubscription($periodic, [
            'subscription_number' => 'SUB-' . strtoupper(substr($periodic->title, 0, 3)) . '-' . date('Y'),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'auto_renewal' => $statusValue === 'active',
            'cost' => $cost,
            'currency' => 'EUR',
            'payment_method' => 'bank_transfer',
            'supplier' => $periodic->publisher,
            'subscription_type' => 'online',
            'status' => $statusValue,
            'notes' => "Abonnement institutionnel pour {$this->organisation->name}",
        ], $this->creator);
    }

    /**
     * Générer des auteurs aléatoires
     */
    private function generateAuthors(int $count): array
    {
        $firstNames = ['John', 'Mary', 'Robert', 'Jennifer', 'Michael', 'Sarah', 'David', 'Emma', 'James', 'Lisa'];
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez'];
        $affiliations = [
            'Harvard University',
            'MIT',
            'Stanford University',
            'Oxford University',
            'Cambridge University',
            'ETH Zurich',
            'University of Tokyo',
            'Sorbonne University',
        ];

        $authors = [];
        for ($i = 0; $i < $count; $i++) {
            $authors[] = [
                'firstname' => $firstNames[array_rand($firstNames)],
                'lastname' => $lastNames[array_rand($lastNames)],
                'affiliation' => $affiliations[array_rand($affiliations)],
            ];
        }

        return $authors;
    }

    /**
     * Générer un titre d'article
     */
    private function generateArticleTitle(string $subjectArea): string
    {
        $titles = [
            'Multidisciplinary Sciences' => [
                'Novel insights into quantum computing applications',
                'Climate change impacts on biodiversity',
                'Advances in CRISPR gene editing techniques',
                'Machine learning approaches to protein folding',
            ],
            'Medicine' => [
                'Clinical outcomes in COVID-19 vaccine trials',
                'Novel treatments for Alzheimer\'s disease',
                'Precision medicine in oncology',
                'Digital health interventions in primary care',
            ],
            'Computer Science' => [
                'Deep learning architectures for natural language processing',
                'Blockchain applications in distributed systems',
                'Quantum algorithms for optimization problems',
                'Cybersecurity threats in IoT ecosystems',
            ],
            'History' => [
                'Renaissance art and political propaganda',
                'Colonial legacies in modern governance',
                'Industrial revolution and social change',
                'Cold War diplomacy in Eastern Europe',
            ],
        ];

        $options = $titles[$subjectArea] ?? $titles['Multidisciplinary Sciences'];
        return $options[array_rand($options)];
    }

    /**
     * Générer un résumé d'article
     */
    private function generateAbstract(string $subjectArea): string
    {
        return "This study investigates important aspects of {$subjectArea}. Our research demonstrates significant findings that contribute to the field. Methods included comprehensive analysis and rigorous experimentation. Results show promising outcomes with implications for future research and practical applications.";
    }
}
