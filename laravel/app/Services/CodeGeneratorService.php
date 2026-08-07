<?php

namespace App\Services;

use App\Models\Communication;
use App\Models\Reservation;
use Carbon\Carbon;

class CodeGeneratorService
{
    /**
     * Génère un code unique pour les communications au format CAAAANNNN
     * C = Communication, AAAA = année en cours, NNNN = numéro d'ordre à 4 chiffres
     *
     * @return string
     */
    public function generateCommunicationCode(): string
    {
        $currentYear = Carbon::now()->format('Y');
        $prefix = 'C' . $currentYear;

        // Trouver le dernier code utilisé pour cette année
        $lastCode = Communication::where('code', 'LIKE', $prefix . '%')
            ->orderByRaw('CAST(SUBSTRING(code, 6) AS UNSIGNED) DESC')
            ->first();

        if ($lastCode) {
            // Extraire le numéro d'ordre et l'incrémenter
            $lastNumber = (int) substr($lastCode->code, 5);
            $nextNumber = $lastNumber + 1;
        } else {
            // Premier code de l'année
            $nextNumber = 1;
        }

        // Formater le numéro d'ordre sur 4 chiffres
        $formattedNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return $prefix . $formattedNumber;
    }

    /**
     * Génère un code unique pour les réservations au format RAAAANNNN
     * R = Réservation, AAAA = année en cours, NNNN = numéro d'ordre à 4 chiffres
     *
     * @return string
     */
    public function generateReservationCode(): string
    {
        $currentYear = Carbon::now()->format('Y');
        $prefix = 'R' . $currentYear;

        // Trouver le dernier code utilisé pour cette année
        $lastCode = Reservation::where('code', 'LIKE', $prefix . '%')
            ->orderByRaw('CAST(SUBSTRING(code, 6) AS UNSIGNED) DESC')
            ->first();

        if ($lastCode) {
            // Extraire le numéro d'ordre et l'incrémenter
            $lastNumber = (int) substr($lastCode->code, 5);
            $nextNumber = $lastNumber + 1;
        } else {
            // Premier code de l'année
            $nextNumber = 1;
        }

        // Formater le numéro d'ordre sur 4 chiffres
        $formattedNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return $prefix . $formattedNumber;
    }
}
