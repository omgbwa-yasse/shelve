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
        // Supprimer l'ancienne table record_book_authors qui contenait name
        Schema::dropIfExists('record_book_authors');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recréer l'ancienne table pour rollback
        Schema::create('record_book_authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')
                ->constrained('record_books')
                ->onDelete('cascade');
            $table->string('name')->comment('Nom de l\'auteur');
            $table->string('role')->default('author')->comment('Rôle (author, editor, translator)');
            $table->integer('display_order')->default(0)->comment('Ordre d\'affichage');
            $table->timestamps();

            $table->index('book_id');
            $table->index('name');
        });
    }
};
