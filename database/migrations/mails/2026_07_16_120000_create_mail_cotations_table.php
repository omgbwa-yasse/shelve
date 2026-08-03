<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cotation d'un courrier entrant à une ou plusieurs directions.
     *
     * Une ligne par direction cotée : chacune porte sa propre instruction, son
     * activité du plan de classement, son affectation et son statut de réception.
     * C'est ce qui permet au DG de suivre la réponse de chaque direction
     * individuellement, et au courrier de n'être clos que lorsque toutes ont répondu.
     */
    public function up(): void
    {
        if (Schema::hasTable('mail_cotations')) {
            return;
        }

        Schema::create('mail_cotations', function (Blueprint $table) {
            $table->id();

            // Rattachements
            $table->foreignId('mail_id')->constrained('mails')->cascadeOnDelete();
            $table->foreignId('organisation_id')->constrained('organisations')->cascadeOnDelete();

            // Consigne du DG pour cette direction
            $table->foreignId('action_id')->nullable()->constrained('mail_actions')->nullOnDelete();
            $table->foreignId('activity_id')->nullable()->constrained('activities')->nullOnDelete();
            $table->text('instruction')->nullable();

            // Suivi de la réception par la direction
            $table->string('status', 20)->default('pending'); // pending | completed
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('coted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Une seule cotation par couple courrier / direction.
            $table->unique(['mail_id', 'organisation_id'], 'mail_cotation_unique');
            // Requêtes du tableau de bord : « ce qui reste à réceptionner dans ma direction ».
            $table->index(['organisation_id', 'status'], 'mail_cotation_org_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_cotations');
    }
};
