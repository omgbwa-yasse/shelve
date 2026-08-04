<?php

namespace App\Services\Mail;

use App\Models\Mail;
use App\Models\MailTypology;
use Illuminate\Support\Facades\DB;

/**
 * Attribution des numéros du registre de courrier.
 *
 * Un numéro de registre est l'identifiant opposable d'un courrier : il ne doit
 * jamais être attribué deux fois. L'attribution se fait donc dans une transaction,
 * sur une ligne de séquence verrouillée, et non par un `count()` suivi d'une boucle
 * de vérification comme le faisaient les cinq implémentations qu'elle remplace.
 *
 * Les formats existants sont conservés à l'identique : les codes déjà attribués
 * restent valides et les séquences s'amorcent à partir d'eux.
 */
class MailCodeService
{
    public const REGISTER_MAIL = 'mail';

    /**
     * Numéro par typologie — format AAAA/CODE/0001.
     */
    public function nextForTypology(int $typologyId): string
    {
        $typology = MailTypology::findOrFail($typologyId);
        $year = (int) date('Y');

        $number = $this->allocate(self::REGISTER_MAIL, $typologyId, $year, function () use ($typology, $year) {
            // Amorçage : on repart du plus grand numéro déjà présent au registre.
            return $this->highestExisting(
                $year . '/' . $typology->code . '/%',
                fn ($code) => (int) substr($code, strrpos($code, '/') + 1)
            );
        });

        return sprintf('%d/%s/%04d', $year, $typology->code, $number);
    }

    /**
     * Numéro d'un courrier créé en réponse — format IN-AAAA-001 / OUT- / INT-.
     */
    public function nextForReply(string $mailType): string
    {
        $prefix = match ($mailType) {
            Mail::TYPE_OUTGOING => 'OUT',
            Mail::TYPE_INCOMING => 'IN',
            default => 'INT',
        };

        $year = (int) date('Y');
        $register = 'reply_' . strtolower($prefix);

        $number = $this->allocate($register, null, $year, function () use ($prefix, $year) {
            return $this->highestExisting(
                $prefix . '-' . $year . '-%',
                fn ($code) => (int) substr($code, strrpos($code, '-') + 1)
            );
        });

        return sprintf('%s-%d-%03d', $prefix, $year, $number);
    }

    /**
     * Numéro d'une suite quelconque (bordereaux, chrono continu…).
     */
    public function next(string $register, ?int $typologyId = null, ?int $year = null): int
    {
        return $this->allocate($register, $typologyId, $year ?? (int) date('Y'), fn () => 0);
    }

    /**
     * Réserve le numéro suivant de la séquence, sous verrou.
     *
     * @param  \Closure():int  $seed  plus grand numéro déjà attribué hors séquence
     */
    protected function allocate(string $register, ?int $typologyId, int $year, \Closure $seed): int
    {
        return DB::transaction(function () use ($register, $typologyId, $year, $seed) {
            $row = DB::table('mail_code_sequences')
                ->where('register', $register)
                ->where('year', $year)
                ->where('typology_id', $typologyId)
                ->lockForUpdate()
                ->first();

            if (! $row) {
                $last = $seed();

                $id = DB::table('mail_code_sequences')->insertGetId([
                    'register' => $register,
                    'year' => $year,
                    'typology_id' => $typologyId,
                    'last_number' => $last,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $row = (object) ['id' => $id, 'last_number' => $last];
            }

            $next = $row->last_number + 1;

            DB::table('mail_code_sequences')
                ->where('id', $row->id)
                ->update(['last_number' => $next, 'updated_at' => now()]);

            return $next;
        });
    }

    /**
     * Plus grand numéro déjà utilisé parmi les codes correspondant au motif.
     *
     * @param  \Closure(string):int  $extract
     */
    protected function highestExisting(string $likePattern, \Closure $extract): int
    {
        $codes = Mail::where('code', 'like', $likePattern)->pluck('code');

        $highest = 0;
        foreach ($codes as $code) {
            $highest = max($highest, $extract($code));
        }

        return $highest;
    }
}
