<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Table de liaison pour gérer plusieurs éditeurs par livre avec leur lieu
     */
    public function up(): void
    {
        Schema::create('record_book_publisher_place', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')
                ->constrained('record_books')
                ->onDelete('cascade')
                ->comment('Référence au livre');
            $table->foreignId('publisher_id')
                ->constrained('record_book_publishers')
                ->onDelete('cascade')
                ->comment('Référence à l\'éditeur');

            $table->string('publication_place')->nullable()->comment('Specific location for this publisher');
            $table->integer('display_order')->default(1)->comment('Display order (1st publisher, 2nd, etc.)');
            $table->string('publisher_role', 50)->nullable()->comment('Role (commercial publisher, distributor, etc.)');

            $table->timestamps();

            // Index
            $table->index('book_id');
            $table->index('publisher_id');
            $table->index('display_order');
            $table->index(['book_id', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_book_publisher_place');
    }
};
