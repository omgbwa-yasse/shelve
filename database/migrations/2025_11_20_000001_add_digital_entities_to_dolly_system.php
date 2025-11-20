<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Table pivot pour dossiers numériques
        Schema::create('dolly_digital_folders', function(Blueprint $table){
            $table->unsignedBigInteger('folder_id')->nullable(false);
            $table->unsignedBigInteger('dolly_id')->nullable(false);
            $table->foreign('folder_id')->references('id')->on('record_digital_folders')->onDelete('cascade');
            $table->foreign('dolly_id')->references('id')->on('dollies')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['folder_id', 'dolly_id']);
            $table->index('dolly_id');
            $table->index('folder_id');
        });

        // Table pivot pour documents numériques
        Schema::create('dolly_digital_documents', function(Blueprint $table){
            $table->unsignedBigInteger('document_id')->nullable(false);
            $table->unsignedBigInteger('dolly_id')->nullable(false);
            $table->foreign('document_id')->references('id')->on('record_digital_documents')->onDelete('cascade');
            $table->foreign('dolly_id')->references('id')->on('dollies')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['document_id', 'dolly_id']);
            $table->index('dolly_id');
            $table->index('document_id');
        });

        // Table pivot pour artefacts
        Schema::create('dolly_artifacts', function(Blueprint $table){
            $table->unsignedBigInteger('artifact_id')->nullable(false);
            $table->unsignedBigInteger('dolly_id')->nullable(false);
            $table->foreign('artifact_id')->references('id')->on('record_artifacts')->onDelete('cascade');
            $table->foreign('dolly_id')->references('id')->on('dollies')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['artifact_id', 'dolly_id']);
            $table->index('dolly_id');
            $table->index('artifact_id');
        });

        // Table pivot pour livres
        Schema::create('dolly_books', function(Blueprint $table){
            $table->unsignedBigInteger('book_id')->nullable(false);
            $table->unsignedBigInteger('dolly_id')->nullable(false);
            $table->foreign('book_id')->references('id')->on('record_books')->onDelete('cascade');
            $table->foreign('dolly_id')->references('id')->on('dollies')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['book_id', 'dolly_id']);
            $table->index('dolly_id');
            $table->index('book_id');
        });

        // Table pivot pour séries d'éditeur
        Schema::create('dolly_book_series', function(Blueprint $table){
            $table->unsignedBigInteger('series_id')->nullable(false);
            $table->unsignedBigInteger('dolly_id')->nullable(false);
            $table->foreign('series_id')->references('id')->on('record_book_publisher_series')->onDelete('cascade');
            $table->foreign('dolly_id')->references('id')->on('dollies')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['series_id', 'dolly_id']);
            $table->index('dolly_id');
            $table->index('series_id');
        });

        // Modifier l'enum category dans la table dollies
        // Pour MySQL/MariaDB
        DB::statement("
            ALTER TABLE dollies
            MODIFY COLUMN category ENUM(
                'mail',
                'transaction',
                'record',
                'slip',
                'building',
                'shelf',
                'container',
                'communication',
                'room',
                'digital_folder',
                'digital_document',
                'artifact',
                'book',
                'book_series'
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dolly_digital_folders');
        Schema::dropIfExists('dolly_digital_documents');
        Schema::dropIfExists('dolly_artifacts');
        Schema::dropIfExists('dolly_books');
        Schema::dropIfExists('dolly_book_series');

        // Restaurer l'enum category original
        DB::statement("
            ALTER TABLE dollies
            MODIFY COLUMN category ENUM(
                'mail',
                'transaction',
                'record',
                'slip',
                'building',
                'shelf',
                'container',
                'communication',
                'room'
            )
        ");
    }
};
