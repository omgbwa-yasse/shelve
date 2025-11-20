<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Table de liaison pour gérer les numéros dans les collections
     */
    public function up(): void
    {
        Schema::create('record_book_collection', function (Blueprint $table) {
            $table->foreignId('book_id')
                ->constrained('record_books')
                ->onDelete('cascade')
                ->comment('Référence au livre');
            $table->foreignId('collection_id')
                ->constrained('record_book_publisher_series')
                ->onDelete('cascade')
                ->comment('Référence à la collection');

            $table->string('collection_number', 50)->nullable()->comment('Number in collection');

            $table->primary(['book_id', 'collection_id']);

            $table->index('book_id');
            $table->index('collection_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_book_collection');
    }
};
