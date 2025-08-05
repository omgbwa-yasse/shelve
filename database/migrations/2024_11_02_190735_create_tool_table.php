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
            Les Outils de gestions
        */

        Schema::create('communicabilities', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable(false)->unique(true);
            $table->string('name', 100)->nullable(false);
            $table->integer('duration')->nullable(false);
            $table->text('description')->nullable(true);
            $table->timestamps();
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable(false)->unique();
            $table->string('name', 255)->nullable(false);
            $table->text('observation')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('communicability_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('activities')->onDelete('set null');
            $table->foreign('communicability_id')->references('id')->on('communicabilities')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('sorts', function (Blueprint $table) {
            $table->id();
            $table->enum('code', ['E', 'T', 'C'])->change();
            $table->string('name', 45)->nullable(false);
            $table->string('description', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('retentions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable(false);
            $table->string('name', 200)->nullable(false);
            $table->integer('duration')->nullable(false);
            $table->unsignedBigInteger('sort_id')->nullable(false);
            $table->timestamps();
            $table->foreign('sort_id')->references('id')->on('sorts')->onDelete('cascade');
        });

        Schema::create('law_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200)->nullable(false);
            $table->text('description')->nullable(true);
            $table->timestamps();
        });

        Schema::create('laws', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable(false);
            $table->string('name', 200)->nullable(false);
            $table->text('description')->nullable(true);
            $table->date('publish_date')->nullable(false);
            $table->unsignedBigInteger('law_type_id')->nullable(false);
            $table->timestamps();
            $table->foreign('law_type_id')->references('id')->on('law_types')->onDelete('cascade'); // Correction de law_id en law_type_id
        });

        Schema::create('law_articles', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable(false);
            $table->string('name', 200)->nullable(false);
            $table->text('description')->nullable(true);
            $table->unsignedBigInteger('law_id')->nullable(false);
            $table->timestamps();
            $table->foreign('law_id')->references('id')->on('laws')->onDelete('cascade');
        });

        Schema::create('retention_law_articles', function (Blueprint $table) {
            $table->unsignedBigInteger('retention_id')->nullable(false);
            $table->unsignedBigInteger('law_article_id')->nullable(false);
            $table->primary(['retention_id', 'law_article_id']);
            $table->timestamps();
            $table->foreign('retention_id')->references('id')->on('retentions')->onDelete('cascade');
            $table->foreign('law_article_id')->references('id')->on('law_articles')->onDelete('cascade');
        });

        Schema::create('retention_activity', function (Blueprint $table) {
            $table->unsignedBigInteger('retention_id')->nullable(false);
            $table->unsignedBigInteger('activity_id')->nullable(false);
            $table->primary(['retention_id', 'activity_id']);
            $table->foreign('retention_id')->references('id')->on('retentions')->onDelete('cascade');
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
        });

        Schema::create('organisations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable(false);
            $table->string('name', 200)->nullable(false);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('organisations')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('organisation_activity', function (Blueprint $table) {
            $table->unsignedBigInteger('organisation_id')->nullable(false);
            $table->unsignedBigInteger('activity_id')->nullable(false);
            $table->unsignedBigInteger('creator_id')->nullable(false);
            $table->primary(['organisation_id', 'activity_id']);
            $table->timestamps();
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisation_activity');
        Schema::dropIfExists('organisations');
        Schema::dropIfExists('retention_activity');
        Schema::dropIfExists('retention_law_articles');
        Schema::dropIfExists('law_articles');
        Schema::dropIfExists('laws');
        Schema::dropIfExists('law_types');
        Schema::dropIfExists('retentions');
        Schema::dropIfExists('sorts');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('communicabilities');
    }
};
