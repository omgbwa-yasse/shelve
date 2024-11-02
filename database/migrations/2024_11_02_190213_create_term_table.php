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

        /*
            Thésaurus
        */

        Schema::create('term_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('language_id')->nullable(false);
            $table->unsignedBigInteger('category_id')->nullable(false);
            $table->unsignedBigInteger('type_id')->nullable(false);
            $table->unsignedBigInteger('parent_id')->nullable(false);
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('term_categories')->onDelete('set null');
            $table->foreign('type_id')->references('id')->on('term_typologies')->onDelete('set null');
            $table->foreign('parent_id')->references('id')->on('terms')->onDelete('set null');
        });

        Schema::create('term_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('term_translations', function (Blueprint $table) {
            $table->unsignedBigInteger('term1_id')->nullable(false);
            $table->unsignedBigInteger('term1_language_id')->nullable(false);
            $table->unsignedBigInteger('term2_id')->nullable(false);
            $table->unsignedBigInteger('term2_language_id')->nullable(false);
            $table->primary(['term1_id', 'term2_id']);
            $table->foreign('term1_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('term1_language_id')->references('id')->on('languages')->onDelete('cascade');
            $table->foreign('term2_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('term2_language_id')->references('id')->on('languages')->onDelete('cascade');
        });

        Schema::create('term_equivalent_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5)->nullable(false);
            $table->string('name', 100)->nullable(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('term_equivalent', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('term_id')->nullable();
            $table->string('term_used', 100)->nullable(false);
            $table->unsignedBigInteger('equivalent_type_id')->nullable(false);
            $table->timestamps();
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('equivalent_type_id')->references('id')->on('term_equivalent_types')->onDelete('cascade');
        });

        Schema::create('term_related', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('term_id')->nullable(false);
            $table->unsignedBigInteger('term_related_id')->nullable(false);
            $table->timestamps();
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('term_related_id')->references('id')->on('terms')->onDelete('cascade');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('term');
    }
};
