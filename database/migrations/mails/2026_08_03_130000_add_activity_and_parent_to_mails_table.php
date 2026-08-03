<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Deux ajouts à la table des courriers :
     *
     * - activity_id    : rattache le courrier à une activité du plan de classement
     *                    (lien avec le classement et la durée de conservation) ;
     * - parent_mail_id : chaîne les courriers entre eux. Une réponse ou une suite
     *                    donnée garde le lien vers le courrier d'origine, si bien
     *                    qu'un courrier reçu peut déboucher sur plusieurs courriers
     *                    tout en restant traçable.
     */
    public function up(): void
    {
        Schema::table('mails', function (Blueprint $table) {
            if (!Schema::hasColumn('mails', 'activity_id')) {
                $table->foreignId('activity_id')->nullable()->after('typology_id')
                    ->constrained('activities')->nullOnDelete();
            }

            if (!Schema::hasColumn('mails', 'parent_mail_id')) {
                $table->foreignId('parent_mail_id')->nullable()->after('activity_id')
                    ->constrained('mails')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('mails', function (Blueprint $table) {
            if (Schema::hasColumn('mails', 'parent_mail_id')) {
                $table->dropConstrainedForeignId('parent_mail_id');
            }

            if (Schema::hasColumn('mails', 'activity_id')) {
                $table->dropConstrainedForeignId('activity_id');
            }
        });
    }
};
