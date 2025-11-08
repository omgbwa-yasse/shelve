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
        Schema::create('record_digital_folder_thesaurus_concept', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('folder_id')->nullable(false);
            $table->unsignedBigInteger('concept_id')->nullable(false);
            $table->decimal('weight', 3, 2)->default(0.5)->comment('Relevance weight 0.00-1.00');
            $table->text('context')->nullable()->comment('Context where concept applies');
            $table->string('extraction_note')->nullable()->comment('Notes on concept extraction');
            $table->timestamps();
            $table->unique(['folder_id', 'concept_id'], 'rdfc_folder_concept_unique');
            $table->foreign('folder_id')->references('id')->on('record_digital_folders')->onDelete('cascade');
            $table->foreign('concept_id')->references('id')->on('thesaurus_concepts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_digital_folder_thesaurus_concept');
    }
};
