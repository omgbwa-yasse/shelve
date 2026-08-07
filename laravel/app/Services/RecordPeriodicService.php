<?php

namespace App\Services;

use App\Models\RecordPeriodic;
use App\Models\RecordPeriodicIssue;
use App\Models\RecordPeriodicArticle;
use App\Models\RecordPeriodicSubscription;
use App\Models\User;
use App\Models\Organisation;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Exception;

/**
 * Service de gestion des périodiques (Phase 8)
 *
 * Fonctionnalités:
 * - Gestion des revues/magazines
 * - Gestion des numéros (issues)
 * - Gestion des articles
 * - Gestion des abonnements
 * - Recherche et statistiques
 */
class RecordPeriodicService
{
    /**
     * Créer un périodique
     */
    public function createPeriodic(
        array $data,
        User $creator,
        Organisation $organisation
    ): RecordPeriodic {
        DB::beginTransaction();
        try {
            // Générer le code si non fourni
            if (!isset($data['code'])) {
                $data['code'] = $this->generateCode($organisation);
            }

            $periodic = RecordPeriodic::create([
                'code' => $data['code'],
                'title' => $data['title'],
                'subtitle' => $data['subtitle'] ?? null,
                'description' => $data['description'] ?? null,
                'issn' => $data['issn'] ?? null,
                'eissn' => $data['eissn'] ?? null,
                'type' => $data['type'] ?? null,
                'subject_area' => $data['subject_area'] ?? null,
                'keywords' => $data['keywords'] ?? [],
                'publisher' => $data['publisher'] ?? null,
                'publisher_location' => $data['publisher_location'] ?? null,
                'language' => $data['language'] ?? 'fr',
                'frequency' => $data['frequency'] ?? null,
                'first_year' => $data['first_year'] ?? null,
                'last_year' => $data['last_year'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'website' => $data['website'] ?? null,
                'contact_email' => $data['contact_email'] ?? null,
                'metadata' => $data['metadata'] ?? [],
                'access_level' => $data['access_level'] ?? 'public',
                'status' => $data['status'] ?? 'active',
                'creator_id' => $creator->id,
                'organisation_id' => $organisation->id,
            ]);

            DB::commit();
            return $periodic;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Ajouter un numéro (issue) à un périodique
     */
    public function addIssue(RecordPeriodic $periodic, array $data): RecordPeriodicIssue
    {
        DB::beginTransaction();
        try {
            $issue = RecordPeriodicIssue::create([
                'periodic_id' => $periodic->id,
                'issue_number' => $data['issue_number'],
                'volume' => $data['volume'] ?? null,
                'year' => $data['year'],
                'publication_date' => $data['publication_date'] ?? null,
                'season' => $data['season'] ?? null,
                'title' => $data['title'] ?? null,
                'summary' => $data['summary'] ?? null,
                'page_count' => $data['page_count'] ?? null,
                'doi' => $data['doi'] ?? null,
                'cover_image_path' => $data['cover_image_path'] ?? null,
                'status' => $data['status'] ?? 'expected',
                'received_date' => $data['received_date'] ?? null,
                'location' => $data['location'] ?? null,
                'call_number' => $data['call_number'] ?? null,
                'metadata' => $data['metadata'] ?? [],
            ]);

            DB::commit();
            return $issue;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Ajouter un article à un numéro
     */
    public function addArticle(RecordPeriodicIssue $issue, array $data): RecordPeriodicArticle
    {
        DB::beginTransaction();
        try {
            $article = RecordPeriodicArticle::create([
                'issue_id' => $issue->id,
                'periodic_id' => $issue->periodic_id,
                'title' => $data['title'],
                'abstract' => $data['abstract'] ?? null,
                'authors' => $data['authors'],
                'page_start' => $data['page_start'] ?? null,
                'page_end' => $data['page_end'] ?? null,
                'section' => $data['section'] ?? null,
                'doi' => $data['doi'] ?? null,
                'url' => $data['url'] ?? null,
                'keywords' => $data['keywords'] ?? [],
                'language' => $data['language'] ?? 'fr',
                'article_type' => $data['article_type'] ?? null,
                'metadata' => $data['metadata'] ?? [],
                'is_peer_reviewed' => $data['is_peer_reviewed'] ?? false,
            ]);

            DB::commit();
            return $article;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Créer un abonnement
     */
    public function createSubscription(
        RecordPeriodic $periodic,
        array $data,
        ?User $responsibleUser = null
    ): RecordPeriodicSubscription {
        DB::beginTransaction();
        try {
            $subscription = RecordPeriodicSubscription::create([
                'periodic_id' => $periodic->id,
                'subscription_number' => $data['subscription_number'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'auto_renewal' => $data['auto_renewal'] ?? false,
                'cost' => $data['cost'],
                'currency' => $data['currency'] ?? 'EUR',
                'payment_method' => $data['payment_method'] ?? null,
                'invoice_number' => $data['invoice_number'] ?? null,
                'supplier' => $data['supplier'],
                'supplier_contact' => $data['supplier_contact'] ?? null,
                'subscription_type' => $data['subscription_type'] ?? 'print',
                'access_notes' => $data['access_notes'] ?? null,
                'status' => $data['status'] ?? 'pending',
                'notes' => $data['notes'] ?? null,
                'responsible_user_id' => $responsibleUser?->id,
            ]);

            DB::commit();
            return $subscription;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Rechercher des périodiques
     */
    public function searchPeriodics(array $filters): Collection
    {
        $query = RecordPeriodic::query()->with(['issues', 'subscriptions']);

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['subject_area'])) {
            $query->where('subject_area', $filters['subject_area']);
        }

        if (isset($filters['issn'])) {
            $query->where('issn', $filters['issn']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'LIKE', "%{$filters['search']}%")
                    ->orWhere('subtitle', 'LIKE', "%{$filters['search']}%")
                    ->orWhere('description', 'LIKE', "%{$filters['search']}%")
                    ->orWhere('publisher', 'LIKE', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('title')->get();
    }

    /**
     * Rechercher des numéros
     */
    public function searchIssues(array $filters): Collection
    {
        $query = RecordPeriodicIssue::query()->with('periodic');

        if (isset($filters['periodic_id'])) {
            $query->where('periodic_id', $filters['periodic_id']);
        }

        if (isset($filters['year'])) {
            $query->where('year', $filters['year']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('publication_date', 'desc')->get();
    }

    /**
     * Rechercher des articles
     */
    public function searchArticles(array $filters): Collection
    {
        $query = RecordPeriodicArticle::query()->with(['issue', 'periodic']);

        if (isset($filters['periodic_id'])) {
            $query->where('periodic_id', $filters['periodic_id']);
        }

        if (isset($filters['issue_id'])) {
            $query->where('issue_id', $filters['issue_id']);
        }

        if (isset($filters['article_type'])) {
            $query->where('article_type', $filters['article_type']);
        }

        if (isset($filters['is_peer_reviewed'])) {
            $query->where('is_peer_reviewed', $filters['is_peer_reviewed']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'LIKE', "%{$filters['search']}%")
                    ->orWhere('abstract', 'LIKE', "%{$filters['search']}%");
            });
        }

        return $query->get();
    }

    /**
     * Obtenir les abonnements expirant bientôt
     */
    public function getExpiringSoonSubscriptions(int $days = 30): Collection
    {
        return RecordPeriodicSubscription::expiringSoon($days)
            ->with('periodic')
            ->orderBy('end_date')
            ->get();
    }

    /**
     * Obtenir les numéros manquants
     */
    public function getMissingIssues(): Collection
    {
        return RecordPeriodicIssue::missing()
            ->with('periodic')
            ->orderBy('year', 'desc')
            ->orderBy('issue_number', 'desc')
            ->get();
    }

    /**
     * Obtenir les statistiques
     */
    public function getStatistics(?int $organisationId = null): array
    {
        $periodicQuery = RecordPeriodic::query();

        if ($organisationId) {
            $periodicQuery->where('organisation_id', $organisationId);
        }

        $totalPeriodics = $periodicQuery->count();
        $activePeriodics = (clone $periodicQuery)->where('is_active', true)->count();
        $ceasedPeriodics = (clone $periodicQuery)->where('is_active', false)->count();

        $byType = (clone $periodicQuery)
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        $activeSubscriptions = RecordPeriodicSubscription::active()->count();
        $expiringSubscriptions = RecordPeriodicSubscription::expiringSoon(30)->count();

        $totalIssues = RecordPeriodicIssue::count();
        $cataloguedIssues = RecordPeriodicIssue::catalogued()->count();
        $missingIssues = RecordPeriodicIssue::missing()->count();

        $totalArticles = RecordPeriodicArticle::count();
        $peerReviewedArticles = RecordPeriodicArticle::peerReviewed()->count();

        return [
            'total_periodics' => $totalPeriodics,
            'active_periodics' => $activePeriodics,
            'ceased_periodics' => $ceasedPeriodics,
            'by_type' => $byType,
            'active_subscriptions' => $activeSubscriptions,
            'expiring_subscriptions' => $expiringSubscriptions,
            'total_issues' => $totalIssues,
            'catalogued_issues' => $cataloguedIssues,
            'missing_issues' => $missingIssues,
            'total_articles' => $totalArticles,
            'peer_reviewed_articles' => $peerReviewedArticles,
        ];
    }

    /**
     * Générer un code unique (PER-YYYY-NNNN)
     */
    private function generateCode(Organisation $organisation): string
    {
        $year = date('Y');
        $prefix = 'PER-' . $year . '-';

        $lastPeriodic = RecordPeriodic::where('organisation_id', $organisation->id)
            ->where('code', 'LIKE', $prefix . '%')
            ->orderBy('code', 'desc')
            ->first();

        if ($lastPeriodic) {
            $lastNumber = (int) substr($lastPeriodic->code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
