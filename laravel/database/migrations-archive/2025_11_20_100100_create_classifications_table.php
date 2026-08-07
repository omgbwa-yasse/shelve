<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Table des classifications simplifiée
     */
    public function up(): void
    {
        Schema::create('classifications', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Classification name');
            $table->text('description')->nullable()->comment('Description');
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('classifications')
                ->onDelete('set null')
                ->comment('Parent for hierarchy');
            $table->timestamps();

            // Index
            $table->index('name');
            $table->index('parent_id');
        });

        // Table de liaison notices <-> classifications
        Schema::create('record_book_classification', function (Blueprint $table) {
            $table->foreignId('book_id')
                ->constrained('record_books')
                ->onDelete('cascade')
                ->comment('Reference to book');
            $table->foreignId('classification_id')
                ->constrained('classifications')
                ->onDelete('cascade')
                ->comment('Reference to classification');
            $table->integer('display_order')->default(1)->comment('Display order');

            $table->primary(['book_id', 'classification_id']);
            $table->index('book_id');
            $table->index('classification_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_book_classification');
        Schema::dropIfExists('classifications');
    }
};
