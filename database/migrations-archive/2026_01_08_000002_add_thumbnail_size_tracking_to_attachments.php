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
        Schema::table('attachments', function (Blueprint $table) {
            // Ajouter des colonnes pour suivi de la compression des vignettes
            if (!Schema::hasColumn('attachments', 'thumbnail_size_bytes')) {
                $table->integer('thumbnail_size_bytes')->nullable()->after('thumbnail_error')
                    ->comment('Taille de la vignette en bytes (max 10KB)');
            }

            if (!Schema::hasColumn('attachments', 'thumbnail_density_ppi')) {
                $table->integer('thumbnail_density_ppi')->default(60)->after('thumbnail_size_bytes')
                    ->comment('Densité de la vignette en PPI (pixels par pouce)');
            }

            if (!Schema::hasColumn('attachments', 'thumbnail_compression_quality')) {
                $table->integer('thumbnail_compression_quality')->default(60)->after('thumbnail_density_ppi')
                    ->comment('Qualité de compression JPEG (0-100)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropColumn([
                'thumbnail_size_bytes',
                'thumbnail_density_ppi',
                'thumbnail_compression_quality'
            ]);
        });
    }
};
