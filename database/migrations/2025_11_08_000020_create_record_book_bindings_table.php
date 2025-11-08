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
        // Create book bindings table
        Schema::create('record_book_bindings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Binding name (e.g., broché, relié, spirale)');
            $table->string('name_en')->nullable()->comment('Binding name in English');
            $table->string('description')->nullable()->comment('Binding description');
            $table->string('category', 50)->nullable()->comment('soft, hard, spiral, etc.');
            $table->integer('durability_rating')->nullable()->comment('Durability rating 1-10');
            $table->decimal('relative_cost', 3, 2)->nullable()->comment('Relative cost multiplier (1.0 = standard)');
            $table->text('notes')->nullable()->comment('Additional notes');
            $table->integer('total_books')->default(0)->comment('Number of books with this binding');
            $table->enum('status', ['active', 'deprecated', 'historical'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('name');
            $table->index('category');
            $table->index('durability_rating');
            $table->index('status');
            $table->fullText(['name', 'name_en', 'description']);
        });

        // Add binding_id foreign key to record_books
        Schema::table('record_books', function (Blueprint $table) {
            $table->foreignId('binding_id')
                ->nullable()
                ->after('binding')
                ->constrained('record_book_bindings')
                ->nullOnDelete();

            $table->index('binding_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('record_books', function (Blueprint $table) {
            $table->dropForeign(['binding_id']);
            $table->dropColumn('binding_id');
        });

        Schema::dropIfExists('record_book_bindings');
    }
};
