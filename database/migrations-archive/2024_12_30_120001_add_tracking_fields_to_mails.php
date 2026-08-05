<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter les nouvelles colonnes à la table mails existante
        Schema::table('mails', function (Blueprint $table) {
            $table->timestamp('deadline')->nullable()->after('is_archived');
            $table->timestamp('processed_at')->nullable()->after('deadline');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null')->after('processed_at');
            $table->timestamp('assigned_at')->nullable()->after('assigned_to');
            $table->integer('estimated_processing_time')->nullable()->comment('Temps estimé en minutes')->after('assigned_at');

            // Modifier le champ status pour utiliser les nouveaux statuts
            $table->enum('status', [
                'draft',
                'pending_review',
                'in_progress',
                'pending_approval',
                'approved',
                'transmitted',
                'completed',
                'rejected',
                'cancelled',
                'overdue'
            ])->default('draft')->change();

            // Index pour les performances
            $table->index('assigned_to');
            $table->index('deadline');
            $table->index(['status', 'assigned_to']);
        });
    }

    public function down(): void
    {
        Schema::table('mails', function (Blueprint $table) {
            $table->dropIndex(['mails_assigned_to_index']);
            $table->dropIndex(['mails_deadline_index']);
            $table->dropIndex(['mails_status_assigned_to_index']);

            $table->dropConstrainedForeignId('assigned_to');
            $table->dropColumn([
                'deadline',
                'processed_at',
                'assigned_at',
                'estimated_processing_time'
            ]);

            // Remettre l'ancien enum status
            $table->enum('status', ['draft', 'in_progress', 'transmitted', 'reject'])->default('draft')->change();
        });
    }
};
