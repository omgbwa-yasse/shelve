<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Visibilité des documents d'un workplace : par défaut un document du workplace
     * est invisible du module Records (`is_workplace_shared = false`). Passer le
     * flag à `true` le partage au module Records (visible mais toujours dans le
     * workplace). Le transfert vers Records pose `workplace_id = null` : le document
     * quitte alors l'espace et n'est plus listable que via le module Records.
     */
    public function up(): void
    {
        Schema::table('records', function (Blueprint $table) {
            $table->boolean('is_workplace_shared')
                ->default(false)
                ->after('is_workplace_folder');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('records', function (Blueprint $table) {
            $table->dropColumn('is_workplace_shared');
        });
    }
};
