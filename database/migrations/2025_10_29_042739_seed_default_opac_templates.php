<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Database\Seeders\OpacTemplatesSeeder;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Exécuter le seeder pour créer les templates OPAC par défaut
        $seeder = new OpacTemplatesSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les templates OPAC créés par défaut
        \App\Models\PublicTemplate::where('type', 'opac')
            ->whereIn('name', [
                'Default Classic',
                'Modern Minimal',
                'Academic Pro',
                'Dark Theme',
                'Colorful Creative'
            ])
            ->delete();
    }
};
