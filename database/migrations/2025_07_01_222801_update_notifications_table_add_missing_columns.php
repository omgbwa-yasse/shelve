<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Vérifions d'abord si les colonnes n'existent pas avant de les ajouter
            if (!Schema::hasColumn('notifications', 'notifiable_type')) {
                $table->string('notifiable_type')->nullable()->after('id');
            }

            if (!Schema::hasColumn('notifications', 'notifiable_id')) {
                $table->unsignedBigInteger('notifiable_id')->nullable()->after('notifiable_type');
            }

            if (!Schema::hasColumn('notifications', 'type')) {
                $table->string('type')->nullable()->after('id');
            }

            if (!Schema::hasColumn('notifications', 'data')) {
                $table->text('data')->nullable()->after('notifiable_id');
            }

            if (!Schema::hasColumn('notifications', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('data');
            }

            // Ajout d'un index pour la recherche plus rapide
            $table->index(['notifiable_type', 'notifiable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Suppression de l'index
            $table->dropIndex(['notifiable_type', 'notifiable_id']);

            // Suppression des colonnes
            $table->dropColumn([
                'notifiable_type',
                'notifiable_id',
                'type',
                'data',
                'read_at'
            ]);
        });
    }
};
