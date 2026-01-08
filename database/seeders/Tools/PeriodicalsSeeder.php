<?php

namespace Database\Seeders\Tools;

use App\Models\RecordPeriodical;
use App\Models\RecordPeriodicalIssue;
use App\Models\RecordPeriodicalLoan;
use App\Models\RecordPeriodicalSubscription;
use App\Models\RecordPeriodicalClaim;
use App\Models\User;
use App\Models\Organisation;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PeriodicalsSeeder extends Seeder
{
    public function run(): void
    {
        // Get first user and organisation
        $user = User::first();
        $organisation = Organisation::first();

        if (!$user || !$organisation) {
            $this->command->warn('Please ensure at least one user and organisation exist before running this seeder.');
            return;
        }

        // 1. Monthly Magazine
        $magazine = RecordPeriodical::create([
            'issn' => '1234-5678',
            'title' => 'National Geographic',
            'subtitle' => 'The Official Journal of the National Geographic Society',
            'abbreviated_title' => 'Nat Geo',
            'publisher' => 'National Geographic Partners',
            'place_of_publication' => 'Washington, D.C.',
            'start_year' => 1888,
            'dewey' => '910.5',
            'subjects' => json_encode(['Geography', 'Science', 'Nature', 'Photography']),
            'frequency' => 'monthly',
            'periodical_type' => 'magazine',
            'format' => 'Print',
            'language' => 'English',
            'is_subscribed' => true,
            'subscription_start' => Carbon::now()->subYear(),
            'subscription_end' => Carbon::now()->addMonths(6),
            'subscription_price' => 59.99,
            'supplier' => 'Magazine Distributors Inc.',
            'description' => 'A prestigious magazine featuring articles on geography, science, nature, and culture.',
            'scope' => 'International coverage of natural and cultural phenomena',
            'website' => 'https://www.nationalgeographic.com',
            'editor_in_chief' => 'Susan Goldberg',
            'access_level' => 'public',
            'status' => 'active',
            'creator_id' => $user->id,
            'organisation_id' => $organisation->id,
        ]);

        // Create active subscription
        RecordPeriodicalSubscription::create([
            'periodical_id' => $magazine->id,
            'subscription_number' => 'SUB-2024-001',
            'start_date' => Carbon::now()->subYear(),
            'end_date' => Carbon::now()->addMonths(6),
            'subscription_type' => 'institutional',
            'price' => 59.99,
            'currency' => 'USD',
            'supplier' => 'Magazine Distributors Inc.',
            'order_number' => 'ORD-MAG-2024-001',
            'status' => 'active',
            'auto_renew' => true,
        ]);

        // Create recent issues
        for ($i = 3; $i >= 0; $i--) {
            $pubDate = Carbon::now()->subMonths($i)->startOfMonth();
            RecordPeriodicalIssue::create([
                'periodical_id' => $magazine->id,
                'volume' => 235,
                'issue_number' => 12 - $i,
                'publication_date' => $pubDate,
                'publication_year' => $pubDate->year,
                'publication_month' => $pubDate->month,
                'pages' => 120,
                'cover_theme' => 'Wildlife Photography Issue ' . (12 - $i),
                'receipt_date' => $pubDate->addDays(5),
                'receipt_status' => 'received',
                'status' => 'available',
                'is_on_loan' => false,
                'location' => 'Periodicals Section',
                'shelf' => 'MAG-A1',
                'barcode' => 'NG-' . str_pad(235, 3, '0', STR_PAD_LEFT) . '-' . str_pad(12 - $i, 2, '0', STR_PAD_LEFT),
            ]);
        }

        // Create one expected issue
        $nextMonth = Carbon::now()->addMonth()->startOfMonth();
        RecordPeriodicalIssue::create([
            'periodical_id' => $magazine->id,
            'volume' => 236,
            'issue_number' => 1,
            'publication_date' => $nextMonth,
            'publication_year' => $nextMonth->year,
            'publication_month' => $nextMonth->month,
            'pages' => 120,
            'receipt_status' => 'expected',
            'status' => 'available',
            'is_on_loan' => false,
            'location' => 'Periodicals Section',
            'shelf' => 'MAG-A1',
        ]);

        $magazine->updateIssueStatistics();

        // 2. Quarterly Academic Journal
        $journal = RecordPeriodical::create([
            'issn' => '2345-6789',
            'title' => 'Journal of Information Science',
            'subtitle' => null,
            'abbreviated_title' => 'J. Inf. Sci.',
            'publisher' => 'Academic Press',
            'place_of_publication' => 'London, UK',
            'start_year' => 1975,
            'dewey' => '020.5',
            'lcc' => 'Z671',
            'subjects' => json_encode(['Library Science', 'Information Science', 'Digital Archives']),
            'frequency' => 'quarterly',
            'periodical_type' => 'journal',
            'format' => 'Print + Digital',
            'language' => 'English',
            'is_subscribed' => true,
            'subscription_start' => Carbon::now()->subMonths(8),
            'subscription_end' => Carbon::now()->addMonths(4),
            'subscription_price' => 299.00,
            'supplier' => 'Academic Journal Services',
            'description' => 'Peer-reviewed journal covering all aspects of information science and librarianship.',
            'scope' => 'Academic research in information management and digital preservation',
            'website' => 'https://www.journalofinformationscience.org',
            'editor_in_chief' => 'Dr. Sarah Johnson',
            'access_level' => 'public',
            'status' => 'active',
            'creator_id' => $user->id,
            'organisation_id' => $organisation->id,
        ]);

        RecordPeriodicalSubscription::create([
            'periodical_id' => $journal->id,
            'subscription_number' => 'SUB-2024-002',
            'start_date' => Carbon::now()->subMonths(8),
            'end_date' => Carbon::now()->addMonths(4),
            'subscription_type' => 'institutional',
            'price' => 299.00,
            'currency' => 'USD',
            'supplier' => 'Academic Journal Services',
            'order_number' => 'ORD-JRN-2024-001',
            'status' => 'active',
            'auto_renew' => true,
        ]);

        // Create quarterly issues
        $quarterMonths = [3, 6, 9, 12];
        foreach ($quarterMonths as $month) {
            $pubDate = Carbon::create(2024, $month, 1);
            if ($pubDate <= Carbon::now()) {
                RecordPeriodicalIssue::create([
                    'periodical_id' => $journal->id,
                    'volume' => 49,
                    'issue_number' => array_search($month, $quarterMonths) + 1,
                    'publication_date' => $pubDate,
                    'publication_year' => 2024,
                    'publication_month' => $month,
                    'pages' => 250,
                    'cover_theme' => 'Digital Preservation Q' . (array_search($month, $quarterMonths) + 1),
                    'receipt_date' => $pubDate->copy()->addDays(10),
                    'receipt_status' => 'received',
                    'status' => 'available',
                    'is_on_loan' => false,
                    'location' => 'Academic Journals',
                    'shelf' => 'JRN-B2',
                    'barcode' => 'JIS-049-' . str_pad(array_search($month, $quarterMonths) + 1, 2, '0', STR_PAD_LEFT),
                ]);
            }
        }

        // Create one missing issue with claim
        $missingIssue = RecordPeriodicalIssue::create([
            'periodical_id' => $journal->id,
            'volume' => 48,
            'issue_number' => 4,
            'publication_date' => Carbon::create(2023, 12, 1),
            'publication_year' => 2023,
            'publication_month' => 12,
            'pages' => 250,
            'receipt_status' => 'claimed',
            'status' => 'available',
            'is_on_loan' => false,
            'location' => 'Academic Journals',
            'shelf' => 'JRN-B2',
        ]);

        RecordPeriodicalClaim::create([
            'periodical_id' => $journal->id,
            'issue_id' => $missingIssue->id,
            'claim_date' => Carbon::now()->subDays(15),
            'claim_type' => 'missing',
            'description' => 'December 2023 issue never received. Expected delivery was mid-December.',
            'status' => 'sent',
            'claimed_by' => $user->id,
        ]);

        $journal->updateIssueStatistics();

        // 3. Weekly Newspaper
        $newspaper = RecordPeriodical::create([
            'issn' => '3456-7890',
            'title' => 'The Science Weekly',
            'subtitle' => 'Latest Discoveries in Science and Technology',
            'abbreviated_title' => 'Sci Weekly',
            'publisher' => 'Science Media Group',
            'place_of_publication' => 'New York, NY',
            'start_year' => 1995,
            'dewey' => '505',
            'subjects' => json_encode(['Science News', 'Technology', 'Research']),
            'frequency' => 'weekly',
            'periodical_type' => 'newspaper',
            'format' => 'Print',
            'language' => 'English',
            'is_subscribed' => false,
            'description' => 'Weekly newspaper covering the latest scientific discoveries and technological advances.',
            'scope' => 'Current science and technology news',
            'website' => 'https://www.scienceweekly.com',
            'editor_in_chief' => 'Michael Chen',
            'access_level' => 'public',
            'status' => 'active',
            'creator_id' => $user->id,
            'organisation_id' => $organisation->id,
        ]);

        // Create recent weekly issues (last 4 weeks)
        for ($i = 4; $i >= 1; $i--) {
            $pubDate = Carbon::now()->subWeeks($i)->startOfWeek();
            RecordPeriodicalIssue::create([
                'periodical_id' => $newspaper->id,
                'issue_number' => 52 - $i,
                'publication_date' => $pubDate,
                'publication_year' => $pubDate->year,
                'publication_month' => $pubDate->month,
                'pages' => 48,
                'cover_theme' => 'Week ' . (52 - $i) . ' - Science News',
                'receipt_date' => $pubDate->addDays(2),
                'receipt_status' => 'received',
                'status' => 'available',
                'is_on_loan' => false,
                'location' => 'Newspapers Section',
                'shelf' => 'NEWS-C1',
                'barcode' => 'SW-2024-' . str_pad(52 - $i, 3, '0', STR_PAD_LEFT),
            ]);
        }

        $newspaper->updateIssueStatistics();

        // 4. Ceased Publication (Historical)
        $ceased = RecordPeriodical::create([
            'issn' => '4567-8901',
            'title' => 'Archives Quarterly',
            'subtitle' => 'A Journal of Archival Practice',
            'abbreviated_title' => 'Arch. Q.',
            'publisher' => 'Historical Archives Press',
            'place_of_publication' => 'Boston, MA',
            'start_year' => 1980,
            'end_year' => 2020,
            'dewey' => '026.5',
            'subjects' => json_encode(['Archival Science', 'Records Management']),
            'frequency' => 'quarterly',
            'periodical_type' => 'journal',
            'format' => 'Print',
            'language' => 'English',
            'is_subscribed' => false,
            'description' => 'Quarterly journal dedicated to archival practice and records management (ceased publication in 2020).',
            'scope' => 'Professional archival practice and theory',
            'access_level' => 'public',
            'status' => 'ceased',
            'creator_id' => $user->id,
            'organisation_id' => $organisation->id,
        ]);

        // Create historical issues
        for ($year = 2018; $year <= 2020; $year++) {
            foreach ([3, 6, 9, 12] as $month) {
                RecordPeriodicalIssue::create([
                    'periodical_id' => $ceased->id,
                    'volume' => $year - 1979,
                    'issue_number' => array_search($month, [3, 6, 9, 12]) + 1,
                    'publication_date' => Carbon::create($year, $month, 1),
                    'publication_year' => $year,
                    'publication_month' => $month,
                    'pages' => 180,
                    'receipt_status' => 'received',
                    'status' => 'available',
                    'is_on_loan' => false,
                    'location' => 'Archives Storage',
                    'shelf' => 'ARCH-D3',
                    'barcode' => 'AQ-' . str_pad($year - 1979, 3, '0', STR_PAD_LEFT) . '-' . str_pad(array_search($month, [3, 6, 9, 12]) + 1, 2, '0', STR_PAD_LEFT),
                ]);
            }
        }

        $ceased->updateIssueStatistics();

        $this->command->info('Periodicals seeded successfully!');
        $this->command->info('- 4 periodicals created (magazine, journal, newspaper, ceased)');
        $this->command->info('- 2 active subscriptions');
        $this->command->info('- Multiple issues per periodical');
        $this->command->info('- 1 claim for missing issue');
    }
}

