<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Champs de validation/signature du Directeur Général sur le courrier sortant,
     * et note explicative jointe lors de la proposition au DG.
     *
     * Le statut de signature est stocké en string (portable SQLite/MySQL) :
     * pending | signed | rejected.
     */
    public function up(): void
    {
        Schema::table('mails', function (Blueprint $table) {
            $table->string('dg_signature_status', 20)->nullable()->after('estimated_processing_time');
            $table->foreignId('dg_signed_by')->nullable()->constrained('users')->onDelete('set null')->after('dg_signature_status');
            $table->timestamp('dg_signed_at')->nullable()->after('dg_signed_by');
            $table->text('dg_signature_note')->nullable()->after('dg_signed_at');
            $table->text('explanatory_note')->nullable()->after('dg_signature_note');
        });
    }

    public function down(): void
    {
        Schema::table('mails', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dg_signed_by');
            $table->dropColumn([
                'dg_signature_status',
                'dg_signed_at',
                'dg_signature_note',
                'explanatory_note',
            ]);
        });
    }
};
