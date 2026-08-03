<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Intérim par volet : un titulaire absent peut être remplacé par PLUSIEURS
     * intérimaires en même temps, chacun sur un périmètre distinct.
     *
     * - activity_id : le volet délégué, pris dans le plan de classement de l'entité
     *   (ex. « Paie et avantages », « Gestion des infrastructures ») ;
     * - scope       : précision libre du volet, en complément de l'activité ;
     * - is_primary  : l'intérimaire vers qui le courrier de l'entité est routé
     *                 par défaut (un seul à la fois par titulaire et par entité).
     */
    public function up(): void
    {
        Schema::table('organisation_interims', function (Blueprint $table) {
            if (!Schema::hasColumn('organisation_interims', 'activity_id')) {
                $table->foreignId('activity_id')->nullable()->after('interim_user_id')
                    ->constrained('activities')->nullOnDelete();
            }

            if (!Schema::hasColumn('organisation_interims', 'scope')) {
                $table->string('scope', 255)->nullable()->after('activity_id');
            }

            if (!Schema::hasColumn('organisation_interims', 'is_primary')) {
                $table->boolean('is_primary')->default(false)->after('scope');
            }
        });
    }

    public function down(): void
    {
        Schema::table('organisation_interims', function (Blueprint $table) {
            if (Schema::hasColumn('organisation_interims', 'is_primary')) {
                $table->dropColumn('is_primary');
            }

            if (Schema::hasColumn('organisation_interims', 'scope')) {
                $table->dropColumn('scope');
            }

            if (Schema::hasColumn('organisation_interims', 'activity_id')) {
                $table->dropConstrainedForeignId('activity_id');
            }
        });
    }
};
