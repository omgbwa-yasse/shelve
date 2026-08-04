<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_skills', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('version', 50)->nullable();
            $table->enum('location', ['system', 'custom'])->default('custom');
            $table->string('folder')->nullable();
            $table->boolean('enabled')->default(true);
            $table->unsignedBigInteger('installed_by')->nullable();
            $table->timestamps();
            $table->foreign('installed_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['location', 'enabled']);
        });

        Schema::create('ai_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('category', 100)->nullable();
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_templates');
        Schema::dropIfExists('ai_skills');
    }
};
