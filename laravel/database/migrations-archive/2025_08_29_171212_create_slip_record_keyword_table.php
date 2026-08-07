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
        Schema::create('slip_record_keyword', function (Blueprint $table) {
            $table->unsignedBigInteger('slip_record_id')->nullable(false);
            $table->unsignedBigInteger('keyword_id')->nullable(false);
            $table->timestamps();

            // Clé primaire composite
            $table->primary(['slip_record_id', 'keyword_id']);

            // Clés étrangères
            $table->foreign('slip_record_id')->references('id')->on('slip_records')->onDelete('cascade');
            $table->foreign('keyword_id')->references('id')->on('keywords')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slip_record_keyword');
    }
};
