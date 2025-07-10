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
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->string('preferred_label')->index();
            $table->text('scope_note')->nullable();
            $table->string('language', 5)->default('fr')->index();
            $table->string('category')->nullable()->index();
            $table->enum('status', ['candidate', 'approved', 'deprecated'])->default('candidate')->index();
            $table->string('notation')->nullable()->unique();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Index composés pour optimiser les recherches
            $table->index(['language', 'status']);
            $table->index(['preferred_label', 'language']);
            
            // Clés étrangères
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terms');
    }
};
