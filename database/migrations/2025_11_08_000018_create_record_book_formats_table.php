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
        // Create book formats table
        Schema::create('record_book_formats', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Format name (e.g., in-8, in-4, A4, Pocket)');
            $table->string('name_en')->nullable()->comment('Format name in English');
            $table->string('description')->nullable()->comment('Format description');
            $table->string('category', 50)->nullable()->comment('pocket, standard, large, folio, etc.');
            $table->decimal('width_cm', 5, 2)->nullable()->comment('Average width in centimeters');
            $table->decimal('height_cm', 5, 2)->nullable()->comment('Average height in centimeters');
            $table->string('dimensions_range')->nullable()->comment('Typical dimensions range');
            $table->text('notes')->nullable()->comment('Additional notes');
            $table->integer('total_books')->default(0)->comment('Number of books in this format');
            $table->enum('status', ['active', 'deprecated', 'historical'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('name');
            $table->index('category');
            $table->index('status');
            $table->fullText(['name', 'name_en', 'description']);
        });

        // Add format_id foreign key to record_books
        Schema::table('record_books', function (Blueprint $table) {
            $table->foreignId('format_id')
                ->nullable()
                ->after('format')
                ->constrained('record_book_formats')
                ->nullOnDelete();

            $table->index('format_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('record_books', function (Blueprint $table) {
            $table->dropForeign(['format_id']);
            $table->dropColumn('format_id');
        });

        Schema::dropIfExists('record_book_formats');
    }
};
