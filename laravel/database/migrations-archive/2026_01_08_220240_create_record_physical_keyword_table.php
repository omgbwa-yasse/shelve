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
        if (!Schema::hasTable('record_physical_keyword')) {
            Schema::create('record_physical_keyword', function (Blueprint $table) {
                $table->unsignedBigInteger('record_physical_id');
                $table->unsignedBigInteger('keyword_id');
                $table->timestamps();

                $table->primary(['record_physical_id', 'keyword_id']);
                $table->foreign('record_physical_id')->references('id')->on('record_physicals')->onDelete('cascade');
                $table->foreign('keyword_id')->references('id')->on('keywords')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_physical_keyword');
    }
};
