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
        Schema::table('public_news', function (Blueprint $table) {
            // Ajouter les champs manquants pour que le contrôleur fonctionne
            $table->string('title')->nullable()->after('name');
            $table->text('summary')->nullable()->after('content');
            $table->string('image_path')->nullable()->after('summary');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->after('is_published');
            $table->boolean('featured')->default(false)->after('status');

            // Ajout d'indexes pour les nouvelles colonnes
            $table->index('status');
            $table->index('featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('public_news', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['featured']);
            $table->dropColumn(['title', 'summary', 'image_path', 'status', 'featured']);
        });
    }
};
