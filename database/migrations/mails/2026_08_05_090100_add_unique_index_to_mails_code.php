<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Unicité du numéro de registre.
 *
 * `mails.code` identifie un courrier au registre arrivée/départ ; c'est la donnée
 * opposable en cas de litige. Elle n'avait pourtant aucune contrainte d'unicité :
 * l'unicité reposait sur un `where()->first()` applicatif, présent dans certains
 * contrôleurs seulement, et `MailSendController::transfer()` dupliquait le code
 * volontairement (corrigé : suffixe -T1).
 *
 * Les doublons éventuellement présents sont d'abord désambiguïsés (-D2, -D3…) pour
 * que la contrainte puisse être posée sans perte de données.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mails')) {
            return;
        }

        $this->deduplicateExistingCodes();

        if ($this->indexExists()) {
            return;
        }

        Schema::table('mails', function (Blueprint $table) {
            $table->unique('code', 'mails_code_unique');
        });
    }

    public function down(): void
    {
        if (! $this->indexExists()) {
            return;
        }

        Schema::table('mails', function (Blueprint $table) {
            $table->dropUnique('mails_code_unique');
        });
    }

    /**
     * Suffixe les doublons existants pour rendre la colonne unique.
     */
    private function deduplicateExistingCodes(): void
    {
        $duplicates = DB::table('mails')
            ->select('code')
            ->groupBy('code')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('code');

        foreach ($duplicates as $code) {
            // On garde le premier enregistrement intact : c'est lui qui porte
            // légitimement le numéro. Les suivants sont suffixés.
            $ids = DB::table('mails')->where('code', $code)->orderBy('id')->pluck('id')->slice(1);

            $suffix = 2;
            foreach ($ids as $id) {
                do {
                    $marque = '-D' . $suffix;
                    // La colonne est un varchar(30) : on rogne la base plutôt que
                    // de laisser MySQL tronquer ou rejeter la valeur.
                    $candidate = mb_substr($code, 0, 30 - mb_strlen($marque)) . $marque;
                    $suffix++;
                } while (DB::table('mails')->where('code', $candidate)->exists());

                DB::table('mails')->where('id', $id)->update(['code' => $candidate]);
            }
        }
    }

    /**
     * L'index existe-t-il déjà ?
     *
     * Volontairement agnostique du SGBD : la production tourne sous MySQL et le
     * développement sous SQLite. On passe donc par l'introspection de schéma de
     * Laravel plutôt que par une table système propre à un moteur.
     */
    private function indexExists(): bool
    {
        foreach (Schema::getIndexes('mails') as $index) {
            $name = is_array($index) ? ($index['name'] ?? null) : ($index->name ?? null);

            if ($name === 'mails_code_unique') {
                return true;
            }
        }

        return false;
    }
};
