<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Table de liaison entre livres et termes du thésaurus
     */
    public function up(): void
    {
        Schema::create('record_book_term', function (Blueprint $table) {
            $table->foreignId('book_id')
                ->constrained('record_books')
                ->onDelete('cascade')
                ->comment('Reference to book');
            $table->foreignId('term_id')
                ->constrained('terms')
                ->onDelete('cascade')
                ->comment('Reference to thesaurus term');
            $table->integer('display_order')->default(1)->comment('Display order');

            $table->primary(['book_id', 'term_id']);
            $table->index('book_id');
            $table->index('term_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_book_term');
    }
};
