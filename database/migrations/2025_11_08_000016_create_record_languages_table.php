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
        // Create languages table
        Schema::create('record_languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique()->comment('ISO 639-1, 639-2 or 639-3 code');
            $table->string('name')->comment('Language name in French');
            $table->string('name_en')->comment('Language name in English');
            $table->string('native_name')->nullable()->comment('Language name in its native script');
            $table->string('script', 50)->nullable()->comment('Writing system (Latin, Arabic, Cyrillic, etc.)');
            $table->enum('direction', ['ltr', 'rtl'])->default('ltr')->comment('Text direction');
            $table->string('iso_639_1', 2)->nullable()->comment('ISO 639-1 two-letter code');
            $table->string('iso_639_2', 3)->nullable()->comment('ISO 639-2 three-letter code');
            $table->string('iso_639_3', 3)->nullable()->comment('ISO 639-3 three-letter code');
            $table->integer('total_books')->default(0)->comment('Number of books in this language');
            $table->enum('status', ['active', 'deprecated', 'historical'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('code');
            $table->index('iso_639_1');
            $table->index('iso_639_2');
            $table->index('script');
            $table->index('direction');
            $table->index('status');
            $table->fullText(['name', 'name_en', 'native_name']);
        });

        // Add language_id foreign key to record_books
        Schema::table('record_books', function (Blueprint $table) {
            $table->foreignId('language_id')
                ->nullable()
                ->after('language')
                ->constrained('record_languages')
                ->nullOnDelete();

            $table->index('language_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('record_books', function (Blueprint $table) {
            $table->dropForeign(['language_id']);
            $table->dropColumn('language_id');
        });

        Schema::dropIfExists('record_languages');
    }
};
