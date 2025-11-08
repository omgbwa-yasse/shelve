<?php

namespace App\Services;

use App\Models\RecordBook;
use App\Models\RecordBookCopy;
use App\Models\RecordBookLoan;
use App\Models\RecordBookReservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service de gestion des prêts, réservations et exemplaires de livres
 * Gère le cycle de vie complet du système de circulation
 */
class RecordBookService
{
    /**
     * Créer un nouvel exemplaire (copy) d'un livre
     *
     * @param RecordBook $book
     * @param array $data
     * @return RecordBookCopy
     */
    public function createCopy(RecordBook $book, array $data): RecordBookCopy
    {
        // Générer un code-barres unique si non fourni
        if (!isset($data['barcode'])) {
            $data['barcode'] = $this->generateBarcode($book);
        }

        // Définir les valeurs par défaut
        $data['book_id'] = $book->id;
        $data['status'] = $data['status'] ?? 'available';
        $data['condition'] = $data['condition'] ?? 'good';

        return RecordBookCopy::create($data);
    }

    /**
     * Générer un code-barres unique pour un exemplaire
     *
     * @param RecordBook $book
     * @return string
     */
    private function generateBarcode(RecordBook $book): string
    {
        // Format: BOOK-{book_id}-{timestamp}-{random}
        $timestamp = now()->format('YmdHis');
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
        return "BOOK-{$book->id}-{$timestamp}-{$random}";
    }

    /**
     * Emprunter un livre (créer un prêt)
     *
     * @param RecordBookCopy $copy
     * @param User $borrower
     * @param int $loanDays Durée du prêt en jours (défaut: 14)
     * @param User|null $processedBy Utilisateur qui traite le prêt
     * @return RecordBookLoan
     * @throws \Exception
     */
    public function loanBook(
        RecordBookCopy $copy,
        User $borrower,
        int $loanDays = 14,
        ?User $processedBy = null
    ): RecordBookLoan {
        DB::beginTransaction();
        try {
            // Vérifier que l'exemplaire est disponible
            if ($copy->status !== 'available') {
                throw new \Exception("L'exemplaire n'est pas disponible pour le prêt (statut: {$copy->status})");
            }

            // Calculer la date de retour
            $dueDate = Carbon::now()->addDays($loanDays);

            // Créer le prêt
            $loan = RecordBookLoan::create([
                'copy_id' => $copy->id,
                'borrower_id' => $borrower->id,
                'loan_date' => now(),
                'due_date' => $dueDate,
                'status' => 'active',
                'renewal_count' => 0,
                'librarian_id' => $processedBy?->id,
            ]);

            // Mettre à jour l'exemplaire
            $copy->update([
                'status' => 'on_loan',
                'is_on_loan' => true,
                'current_loan_id' => $loan->id,
                'due_date' => $dueDate,
                'loan_count' => DB::raw('loan_count + 1'),
                'last_loan_date' => now(),
            ]);

            // Si l'utilisateur avait une réservation pour ce livre, la marquer comme remplie
            $this->fulfillReservation($copy->book_id, $borrower->id, $loan->id);

            DB::commit();

            Log::info("Prêt créé", [
                'loan_id' => $loan->id,
                'copy_barcode' => $copy->barcode,
                'borrower_id' => $borrower->id,
                'due_date' => $dueDate->toDateString(),
            ]);

            return $loan;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de la création du prêt: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Retourner un livre (terminer un prêt)
     *
     * @param RecordBookLoan $loan
     * @param string $returnCondition État du livre au retour
     * @param string|null $returnNotes Notes sur le retour
     * @param User|null $returnedTo Utilisateur qui reçoit le retour
     * @return RecordBookLoan
     * @throws \Exception
     */
    public function returnBook(
        RecordBookLoan $loan,
        string $returnCondition = 'good',
        ?string $returnNotes = null,
        ?User $returnedTo = null
    ): RecordBookLoan {
        DB::beginTransaction();
        try {
            if ($loan->status === 'returned') {
                throw new \Exception("Ce prêt a déjà été retourné");
            }

            $copy = $loan->copy;
            $returnDate = now();

            // Calculer les jours de retard
            $daysOverdue = 0;
            if ($returnDate->gt($loan->due_date)) {
                $daysOverdue = $returnDate->diffInDays($loan->due_date);
            }

            // Calculer les frais de retard (exemple: 0.50€ par jour)
            $lateFee = $daysOverdue > 0 ? $daysOverdue * 0.50 : 0;

            // Calculer les frais de dommage si nécessaire
            $damageFee = 0;
            $damageReported = false;
            if (in_array($returnCondition, ['poor', 'damaged'])) {
                $damageReported = true;
                // Frais de dommage selon la condition
                $damageFee = $returnCondition === 'damaged' ? 10.00 : 5.00;

                // Mettre à jour la condition de l'exemplaire
                $copy->update([
                    'condition' => $returnCondition,
                    'condition_notes' => $returnNotes ?? "Dégradation constatée au retour",
                ]);
            }

            $totalFee = $lateFee + $damageFee;

            // Mettre à jour le prêt
            $loan->update([
                'return_date' => $returnDate,
                'actual_return_time' => $returnDate,
                'status' => 'returned',
                'days_overdue' => $daysOverdue,
                'late_fee' => $lateFee,
                'damage_fee' => $damageFee,
                'total_fee' => $totalFee,
                'fee_paid' => $totalFee == 0, // Payé automatiquement si pas de frais
                'return_condition' => $returnCondition,
                'return_notes' => $returnNotes,
                'damage_reported' => $damageReported,
                'returned_to' => $returnedTo?->id,
            ]);

            // Remettre l'exemplaire disponible (ou en réparation si endommagé)
            $newStatus = $returnCondition === 'damaged' ? 'in_repair' : 'available';

            $copy->update([
                'status' => $newStatus,
                'is_on_loan' => false,
                'current_loan_id' => null,
                'due_date' => null,
            ]);

            // Si disponible, vérifier s'il y a une réservation en attente
            if ($newStatus === 'available') {
                $this->processNextReservation($copy);
            }

            DB::commit();

            Log::info("Livre retourné", [
                'loan_id' => $loan->id,
                'copy_barcode' => $copy->barcode,
                'days_overdue' => $daysOverdue,
                'total_fee' => $totalFee,
                'condition' => $returnCondition,
            ]);

            return $loan;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors du retour du livre: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Renouveler un prêt
     *
     * @param RecordBookLoan $loan
     * @param int $additionalDays Jours supplémentaires (défaut: 14)
     * @return RecordBookLoan
     * @throws \Exception
     */
    public function renewLoan(RecordBookLoan $loan, int $additionalDays = 14): RecordBookLoan
    {
        DB::beginTransaction();
        try {
            if ($loan->status !== 'active') {
                throw new \Exception("Seuls les prêts actifs peuvent être renouvelés");
            }

            $maxRenewals = 3; // Valeur par défaut
            if ($loan->renewal_count >= $maxRenewals) {
                throw new \Exception("Nombre maximal de renouvellements atteint ({$maxRenewals})");
            }

            // Vérifier qu'il n'y a pas de réservation en attente pour ce livre
            $hasReservations = RecordBookReservation::where('book_id', $loan->copy->book_id)
                ->where('status', 'pending')
                ->exists();

            if ($hasReservations) {
                throw new \Exception("Ce livre a des réservations en attente, renouvellement impossible");
            }

            // Calculer la nouvelle date de retour
            $newDueDate = Carbon::parse($loan->due_date)->addDays($additionalDays);

            // Mettre à jour le prêt
            $loan->update([
                'due_date' => $newDueDate,
                'renewal_count' => DB::raw('renewal_count + 1'),
                'last_renewal_date' => now(),
                'status' => 'renewed',
            ]);

            // Mettre à jour l'exemplaire
            $loan->copy->update([
                'due_date' => $newDueDate,
            ]);

            DB::commit();

            Log::info("Prêt renouvelé", [
                'loan_id' => $loan->id,
                'renewal_count' => $loan->renewal_count + 1,
                'new_due_date' => $newDueDate->toDateString(),
            ]);

            return $loan->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors du renouvellement: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Réserver un livre
     *
     * @param RecordBook $book
     * @param User $user
     * @param string $priority Priorité (normal, high, urgent)
     * @param bool $isVip Si l'utilisateur est VIP
     * @return RecordBookReservation
     */
    public function reserveBook(
        RecordBook $book,
        User $user,
        string $priority = 'normal',
        bool $isVip = false
    ): RecordBookReservation {
        DB::beginTransaction();
        try {
            // Vérifier si l'utilisateur a déjà une réservation active pour ce livre
            $existingReservation = RecordBookReservation::where('book_id', $book->id)
                ->where('user_id', $user->id)
                ->whereIn('status', ['pending', 'ready'])
                ->first();

            if ($existingReservation) {
                throw new \Exception("Vous avez déjà une réservation active pour ce livre");
            }

            // Créer la réservation
            $reservation = RecordBookReservation::create([
                'book_id' => $book->id,
                'user_id' => $user->id,
                'reservation_date' => now(),
                'expiry_date' => now()->addDays(7),
                'status' => 'pending',
            ]);

            DB::commit();

            Log::info("Réservation créée", [
                'reservation_id' => $reservation->id,
                'book_id' => $book->id,
                'user_id' => $user->id,
            ]);

            return $reservation;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de la réservation: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Annuler une réservation
     *
     * @param RecordBookReservation $reservation
     * @param string $reason Raison de l'annulation
     * @param User|null $cancelledBy Utilisateur qui annule
     * @return RecordBookReservation
     */
    public function cancelReservation(
        RecordBookReservation $reservation,
        string $reason = 'user_request',
        ?User $cancelledBy = null
    ): RecordBookReservation {
        DB::beginTransaction();
        try {
            if ($reservation->status === 'cancelled') {
                throw new \Exception("Cette réservation est déjà annulée");
            }

            if ($reservation->status === 'fulfilled') {
                throw new \Exception("Cette réservation a déjà été remplie, impossible de l'annuler");
            }

            $reservation->update([
                'status' => 'cancelled',
                'notes' => "Annulée: {$reason}",
            ]);

            DB::commit();

            Log::info("Réservation annulée", [
                'reservation_id' => $reservation->id,
                'reason' => $reason,
            ]);

            return $reservation;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de l'annulation de la réservation: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Traiter les prêts en retard (à exécuter quotidiennement via un cron)
     *
     * @return array Statistiques du traitement
     */
    public function processOverdueLoans(): array
    {
        $today = now();
        $stats = [
            'processed' => 0,
            'first_reminders' => 0,
            'second_reminders' => 0,
            'final_notices' => 0,
        ];

        // Récupérer tous les prêts actifs en retard
        $overdueLoans = RecordBookLoan::where('status', 'active')
            ->where('due_date', '<', $today)
            ->get();

        foreach ($overdueLoans as $loan) {
            $daysOverdue = $today->diffInDays($loan->due_date);

            // Calculer les frais de retard
            $lateFee = $daysOverdue * 0.50;

            $loan->update([
                'status' => 'overdue',
                'days_overdue' => $daysOverdue,
                'late_fee' => $lateFee,
                'total_fee' => $lateFee + ($loan->damage_fee ?? 0),
            ]);

            // Envoyer des rappels selon les jours de retard
            if ($daysOverdue >= 1 && !$loan->first_reminder_sent) {
                $loan->update(['first_reminder_sent' => $today]);
                $stats['first_reminders']++;
                // TODO: Envoyer email de rappel
            } elseif ($daysOverdue >= 7 && !$loan->second_reminder_sent) {
                $loan->update(['second_reminder_sent' => $today]);
                $stats['second_reminders']++;
                // TODO: Envoyer email de rappel urgent
            } elseif ($daysOverdue >= 14 && !$loan->final_notice_sent) {
                $loan->update(['final_notice_sent' => $today]);
                $stats['final_notices']++;
                // TODO: Envoyer email de mise en demeure
            }

            $stats['processed']++;
        }

        Log::info("Traitement des prêts en retard", $stats);

        return $stats;
    }

    /**
     * Calculer les frais pour un prêt
     *
     * @param RecordBookLoan $loan
     * @return array
     */
    public function calculateFees(RecordBookLoan $loan): array
    {
        $lateFee = 0;
        $damageFee = $loan->damage_fee ?? 0;

        if ($loan->status === 'overdue' || ($loan->return_date && $loan->return_date->gt($loan->due_date))) {
            $returnDate = $loan->return_date ?? now();
            $daysOverdue = $returnDate->diffInDays($loan->due_date);
            $lateFee = $daysOverdue * 0.50;
        }

        return [
            'late_fee' => $lateFee,
            'damage_fee' => $damageFee,
            'total_fee' => $lateFee + $damageFee,
            'days_overdue' => $loan->days_overdue ?? 0,
        ];
    }

    /**
     * Marquer une réservation comme remplie (privée)
     */
    private function fulfillReservation(int $bookId, int $userId, int $loanId): void
    {
        $reservation = RecordBookReservation::where('book_id', $bookId)
            ->where('user_id', $userId)
            ->where('status', 'ready')
            ->first();

        if ($reservation) {
            $reservation->update([
                'status' => 'fulfilled',
                'notes' => "Prêt créé (loan_id: {$loanId})",
            ]);
        }
    }

    /**
     * Traiter la prochaine réservation en attente (privée)
     */
    private function processNextReservation(RecordBookCopy $copy): void
    {
        $nextReservation = RecordBookReservation::where('book_id', $copy->book_id)
            ->where('status', 'pending')
            ->orderBy('reservation_date')
            ->first();

        if ($nextReservation) {
            $nextReservation->update([
                'status' => 'ready',
                'copy_id' => $copy->id,
                'notified_at' => now(),
                'expiry_date' => now()->addDays(3), // 3 jours pour venir chercher
            ]);

            // Marquer l'exemplaire comme réservé
            $copy->update([
                'status' => 'reserved',
            ]);

            // TODO: Envoyer notification à l'utilisateur
            Log::info("Réservation disponible", [
                'reservation_id' => $nextReservation->id,
                'copy_barcode' => $copy->barcode,
            ]);
        }
    }
}
