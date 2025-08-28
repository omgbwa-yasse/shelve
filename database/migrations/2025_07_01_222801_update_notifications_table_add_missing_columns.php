<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Compute once outside the closure whether the composite index already exists
        $indexName = 'notifications_notifiable_type_notifiable_id_index';
        $database = DB::getDatabaseName();
        $indexExists = DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', 'notifications')
            ->where('index_name', $indexName)
            ->exists();

        Schema::table('notifications', function (Blueprint $table) use ($indexExists, $indexName) {
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

            // Ajout d'un index pour la recherche plus rapide (uniquement s'il n'existe pas déjà)
            if (!$indexExists && Schema::hasColumn('notifications', 'notifiable_type') && Schema::hasColumn('notifications', 'notifiable_id')) {
                $table->index(['notifiable_type', 'notifiable_id'], $indexName);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexName = 'notifications_notifiable_type_notifiable_id_index';
        $database = DB::getDatabaseName();
        $indexExists = DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', 'notifications')
            ->where('index_name', $indexName)
            ->exists();

        Schema::table('notifications', function (Blueprint $table) use ($indexExists, $indexName) {
            // Suppression de l'index (si présent)
            if ($indexExists) {
                $table->dropIndex($indexName);
            }

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
