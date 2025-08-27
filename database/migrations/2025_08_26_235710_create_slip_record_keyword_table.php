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
        // Créer la table keywords si elle n'existe pas déjà
        if (!Schema::hasTable('keywords')) {
            Schema::create('keywords', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // Créer la table pivot record_keyword si elle n'existe pas déjà
        if (!Schema::hasTable('record_keyword')) {
            Schema::create('record_keyword', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('record_id');
                $table->unsignedBigInteger('keyword_id');
                $table->timestamps();

                $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
                $table->foreign('keyword_id')->references('id')->on('keywords')->onDelete('cascade');

                $table->unique(['record_id', 'keyword_id']);
            });
        }

        // Créer la table pivot slip_record_keyword
        Schema::create('slip_record_keyword', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('slip_record_id');
            $table->unsignedBigInteger('keyword_id');
            $table->timestamps();

            $table->foreign('slip_record_id')->references('id')->on('slip_records')->onDelete('cascade');
            $table->foreign('keyword_id')->references('id')->on('keywords')->onDelete('cascade');

            $table->unique(['slip_record_id', 'keyword_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slip_record_keyword');
        Schema::dropIfExists('record_keyword');
        Schema::dropIfExists('keywords');
    }
};
