<?php

namespace App\Console\Commands;

use App\Models\MailCotation;
use Illuminate\Console\Command;

/**
 * Traitement des échéances de cotation : relances avant terme, signalement des
 * retards, escalade hiérarchique.
 *
 * Remplace `mail:process-notifications`, planifiée par l'ancien noyau console mais
 * qui n'a jamais existé. À ce stade la commande recense et journalise ; l'envoi des
 * notifications et l'escalade arrivent avec le lot « relances » (table
 * mail_reminders + MailDeadlineReminder), une fois les échéances de cotation en
 * place.
 */
class MailProcessDeadlines extends Command
{
    protected $signature = 'mail:process-deadlines
                            {--type=all : reminders | overdue | escalation | all}
                            {--days=3 : Fenêtre, en jours, pour les relances avant échéance}';

    protected $description = 'Recense les cotations dont l\'échéance approche ou est dépassée';

    public function handle(): int
    {
        $type = $this->option('type');

        if (! in_array($type, ['all', 'reminders', 'overdue', 'escalation'], true)) {
            $this->error("Type inconnu : {$type}");

            return self::FAILURE;
        }

        // Les échéances de cotation n'existent pas tant que la migration
        // correspondante n'est pas passée : on sort proprement.
        if (! $this->deadlinesAvailable()) {
            $this->info('Échéances de cotation non disponibles : rien à traiter.');

            return self::SUCCESS;
        }

        if ($type === 'all' || $type === 'reminders') {
            $this->reportReminders((int) $this->option('days'));
        }

        if ($type === 'all' || $type === 'overdue') {
            $this->reportOverdue();
        }

        if ($type === 'all' || $type === 'escalation') {
            $this->line('Escalade : à implémenter avec la table mail_reminders.');
        }

        return self::SUCCESS;
    }

    private function deadlinesAvailable(): bool
    {
        return \Illuminate\Support\Facades\Schema::hasColumn('mail_cotations', 'deadline');
    }

    private function reportReminders(int $days): void
    {
        $count = MailCotation::query()
            ->where('status', MailCotation::STATUS_PENDING)
            ->whereNotNull('deadline')
            ->whereBetween('deadline', [now(), now()->addDays($days)])
            ->count();

        $this->info("Cotations à échéance dans {$days} jour(s) : {$count}");
    }

    private function reportOverdue(): void
    {
        $count = MailCotation::query()
            ->where('status', MailCotation::STATUS_PENDING)
            ->whereNotNull('deadline')
            ->where('deadline', '<', now())
            ->count();

        $this->info("Cotations hors délai : {$count}");
    }
}
