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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->unsignedInteger('type_id')->nullable(false)->default(1);
            $table->foreign('type_id')->references('id')->on('user_types')->onDelete('cascade');
        });


        Schema::create('user_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable();
            $table->string('description', 45)->nullable();
            $table->unsignedBigInteger('role_id')->nullable(false);
            $table->primary('id');
            $table->unique('name');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });


        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('repository')->nullable(false);
            $table->tinyInteger('transfer')->nullable(false);
            $table->tinyInteger('communication')->nullable(false);
            $table->tinyInteger('audit')->nullable(false);
            $table->tinyInteger('room')->nullable(false);
            $table->tinyInteger('dolly')->nullable(false);
            $table->tinyInteger('tools')->nullable(false);
            $table->tinyInteger('setting')->nullable(false);
            $table->tinyInteger('mail')->nullable(false);
            $table->primary('id');
        });


        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
