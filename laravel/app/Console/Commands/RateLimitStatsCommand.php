<?php

namespace App\Console\Commands;

use App\Services\RateLimitService;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\RateLimiter;

class RateLimitStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rate-limit:stats {--user-id= : ID de l\'utilisateur à analyser} {--clear= : Effacer les limites pour une action spécifique}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Afficher les statistiques de rate limiting et gérer les limites';

    /**
     * Execute the console command.
     */
    public function handle(RateLimitService $rateLimitService)
    {
        if ($this->option('clear')) {
            $this->clearRateLimit($rateLimitService);
            return;
        }

        $userId = $this->option('user-id');

        if ($userId) {
            $this->showUserStats($rateLimitService, $userId);
        } else {
            $this->showGeneralStats($rateLimitService);
        }
    }

    private function showUserStats(RateLimitService $rateLimitService, int $userId)
    {
        $user = User::find($userId);
        if (!$user) {
            $this->error("Utilisateur avec l'ID {$userId} non trouvé.");
            return;
        }

        $this->info("Statistiques de rate limiting pour {$user->name} (ID: {$userId})");
        $this->line('');

        $stats = $rateLimitService->getStats($userId);

        $headers = ['Action', 'Utilisées/Maximum', 'Disponible dans (secondes)'];
        $rows = [];

        foreach ($stats as $action => $data) {
            $used = $data['max'] - $data['remaining'];
            $status = $data['available_in'] > 0 ? "⚠️  Bloqué" : "✅ OK";

            $rows[] = [
                $action,
                "{$used}/{$data['max']} {$status}",
                $data['available_in'] > 0 ? $data['available_in'] : '-'
            ];
        }

        $this->table($headers, $rows);
    }

    private function showGeneralStats(RateLimitService $rateLimitService)
    {
        $this->info('Statistiques générales de rate limiting');
        $this->line('');

        // Afficher les limites configurées
        $this->info('Limites configurées :');
        $this->line('- Communication create: 10/heure');
        $this->line('- Reservation create: 15/heure');
        $this->line('- Search: 100/heure');
        $this->line('- Export: 5/heure');
        $this->line('- API general: 1000/heure');
        $this->line('');

        // Afficher la configuration du cache
        $this->info('Configuration du cache :');
        $this->line('- Cache par défaut: ' . config('cache.default'));
        $this->line('- Cache rate limiter: ' . config('cache.limiter'));
        $this->line('');

        // Suggestions
        $this->info('Pour voir les statistiques d\'un utilisateur spécifique :');
        $this->line('php artisan rate-limit:stats --user-id=1');
        $this->line('');
        $this->info('Pour effacer les limites d\'une action :');
        $this->line('php artisan rate-limit:stats --clear=communication_create');
    }

    private function clearRateLimit(RateLimitService $rateLimitService)
    {
        $action = $this->option('clear');

        if (!$this->confirm("Êtes-vous sûr de vouloir effacer les limites pour l'action '{$action}' ?")) {
            $this->info('Opération annulée.');
            return;
        }

        // Effacer pour tous les utilisateurs actifs (dernières 24h)
        $users = User::where('updated_at', '>=', now()->subDay())->get();
        $cleared = 0;

        foreach ($users as $user) {
            $key = "{$action}:{$user->id}";
            if (RateLimiter::tooManyAttempts($key, 1)) {
                RateLimiter::clear($key);
                $cleared++;
            }
        }

        $this->info("Limites effacées pour {$cleared} utilisateur(s) pour l'action '{$action}'.");
    }
}
