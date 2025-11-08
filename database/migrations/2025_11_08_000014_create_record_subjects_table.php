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
        // Table des sujets/thèmes normalisée
        Schema::create('record_subjects', function (Blueprint $table) {
            $table->id();

            // Identification
            $table->string('name')->unique()->comment('Nom du sujet');
            $table->string('name_en')->nullable()->comment('Nom en anglais');
            $table->text('description')->nullable()->comment('Description du sujet');

            // Classification
            $table->string('dewey_class')->nullable()->comment('Classe Dewey');
            $table->string('lcc_class')->nullable()->comment('Classe LCC (Library of Congress)');
            $table->string('rameau')->nullable()->comment('Identifiant RAMEAU');
            $table->string('lcsh')->nullable()->comment('Library of Congress Subject Heading');

            // Hiérarchie
            $table->foreignId('parent_id')->nullable()
                ->constrained('record_subjects')
                ->onDelete('set null')
                ->comment('Sujet parent (hiérarchie)');

            // Métadonnées
            $table->json('related_subjects')->nullable()->comment('Sujets connexes (IDs)');
            $table->json('synonyms')->nullable()->comment('Synonymes et variantes');
            $table->integer('total_books')->default(0)->comment('Nombre de livres');
            $table->enum('status', ['active', 'deprecated', 'merged'])->default('active');

            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('name');
            $table->index('dewey_class');
            $table->index('lcc_class');
            $table->index('parent_id');
            $table->index('status');
            $table->fullText(['name', 'name_en', 'description']);
        });

        // Table pivot pour la relation many-to-many entre livres et sujets
        Schema::create('record_book_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')
                ->constrained('record_books')
                ->onDelete('cascade')
                ->comment('Livre');
            $table->foreignId('subject_id')
                ->constrained('record_subjects')
                ->onDelete('cascade')
                ->comment('Sujet');

            $table->integer('relevance')->default(100)->comment('Pertinence (0-100)');
            $table->boolean('is_primary')->default(false)->comment('Sujet principal');

            $table->timestamps();

            // Index
            $table->index('book_id');
            $table->index('subject_id');
            $table->index('is_primary');
            $table->unique(['book_id', 'subject_id'], 'book_subject_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_book_subject');
        Schema::dropIfExists('record_subjects');
    }
};
