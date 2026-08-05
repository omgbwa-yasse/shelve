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
            // Ajouter des colonnes pour mieux gérer les vignettes si elles n'existent pas
            if (!Schema::hasColumn('attachments', 'thumbnail_generated_at')) {
                $table->timestamp('thumbnail_generated_at')->nullable()->after('thumbnail_path')
                    ->comment('Date de génération de la vignette');
            }

            if (!Schema::hasColumn('attachments', 'thumbnail_error')) {
                $table->text('thumbnail_error')->nullable()->after('thumbnail_generated_at')
                    ->comment('Erreur lors de la génération de la vignette');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropColumn(['thumbnail_generated_at', 'thumbnail_error']);
        });
    }
};
