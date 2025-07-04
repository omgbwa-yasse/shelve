<?php

namespace App\Console\Commands;

use App\Models\Communication;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateCodesToNewFormat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'codes:update-format {--year= : L\'année à utiliser pour les codes (par défaut : année en cours)} {--reset-sequence : Réinitialiser la séquence de numérotation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Met à jour tous les codes existants au format CAAAANNNN pour les communications et RAAAANNNN pour les réservations';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $year = $this->option('year') ?? Carbon::now()->format('Y');
        $resetSequence = $this->option('reset-sequence') ?? false;

        $this->info("Mise à jour des codes au format CAAAANNNN et RAAAANNNN pour l'année $year");

        // Mettre à jour les communications
        $commCount = 0;
        $startCommNumber = $resetSequence ? 1 : null;

        $communications = Communication::all();
        $this->info("Traitement de {$communications->count()} communications...");

        foreach ($communications as $index => $communication) {
            $number = $startCommNumber !== null ? $startCommNumber + $index : $index + 1;
            $formattedNumber = str_pad($number, 4, '0', STR_PAD_LEFT);
            $newCode = 'C' . $year . $formattedNumber;

            $communication->update(['code' => $newCode]);
            $commCount++;

            if ($commCount % 100 === 0) {
                $this->info("$commCount communications traitées");
            }
        }

        // Mettre à jour les réservations
        $resCount = 0;
        $startResNumber = $resetSequence ? 1 : null;

        $reservations = Reservation::all();
        $this->info("Traitement de {$reservations->count()} réservations...");

        foreach ($reservations as $index => $reservation) {
            $number = $startResNumber !== null ? $startResNumber + $index : $index + 1;
            $formattedNumber = str_pad($number, 4, '0', STR_PAD_LEFT);
            $newCode = 'R' . $year . $formattedNumber;

            $reservation->update(['code' => $newCode]);
            $resCount++;

            if ($resCount % 100 === 0) {
                $this->info("$resCount réservations traitées");
            }
        }

        $this->info("Mise à jour terminée : $commCount communications et $resCount réservations mises à jour.");

        return 0;
    }
}
