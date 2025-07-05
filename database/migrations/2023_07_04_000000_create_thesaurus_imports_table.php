<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThesaurusImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('thesaurus_imports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type'); // skos, csv, rdf
            $table->string('filename');
            $table->string('status'); // processing, completed, failed
            $table->integer('total_items')->default(0);
            $table->integer('processed_items')->default(0);
            $table->integer('created_items')->default(0);
            $table->integer('updated_items')->default(0);
            $table->integer('error_items')->default(0);
            $table->integer('relationships_created')->default(0);
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('thesaurus_imports');
    }
}
