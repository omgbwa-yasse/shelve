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
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('type_id')->nullable(false);
            $table->string('name', 100)->nullable(false)->unique();
            $table->string('parallel_name', 100)->nullable(true);
            $table->string('other_name', 100)->nullable(true);
            $table->string('lifespan', 100)->nullable(true);
            $table->string('locations', 100)->nullable(true);
            $table->unsignedInteger('parent_id')->nullable(true);
            $table->timestamps();
            $table->foreign('parent_id')->references('id')->on('authors')->onDelete('set null');
            $table->foreign('type_id')->references('id')->on('author_types')->onDelete('cascade');
        });

        Schema::create('author_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false)->unique();
            $table->longText('description')->nullable(false);
            $table->timestamps();
        });


        Schema::create('author_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('author_id')->nullable(true);
            $table->string('phone1')->nullable();
            $table->string('phone2')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('website')->nullable();
            $table->string('fax')->nullable();
            $table->text('other')->nullable();
            $table->string('po_box')->nullable();
            $table->timestamps();
            $table->foreign('author_id')->references('id')->on('authors')->onDelete('set null');
        });

        Schema::create('author_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('type_id')->nullable(false);
            $table->string('name', 100)->nullable(false)->unique();
            $table->string('parallel_name', 100)->nullable(true);
            $table->string('other_name', 100)->nullable(true);
            $table->string('lifespan', 100)->nullable(true);
            $table->string('locations', 100)->nullable(true);
            $table->unsignedInteger('parent_id')->nullable(true);
            $table->timestamps();
            $table->foreign('parent_id')->references('id')->on('authors')->onDelete('set null');
            $table->foreign('type_id')->references('id')->on('author_types')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('author_types');
        Schema::dropIfExists('authors');
        Schema::dropIfExists('author_contacts');
        Schema::dropIfExists('author_addresses');
    }
};
