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
                    $candidate = $code . '-D' . $suffix;
                    $suffix++;
                } while (DB::table('mails')->where('code', $candidate)->exists());

                DB::table('mails')->where('id', $id)->update(['code' => $candidate]);
            }
        }
    }

    private function indexExists(): bool
    {
        foreach (DB::select("SELECT name FROM sqlite_master WHERE type='index' AND tbl_name='mails'") as $index) {
            if ($index->name === 'mails_code_unique') {
                return true;
            }
        }

        // Sur un SGBD autre que SQLite, on s'appuie sur le schéma Doctrine.
        try {
            return Schema::hasIndex('mails', 'mails_code_unique');
        } catch (\Throwable) {
            return false;
        }
    }
};
