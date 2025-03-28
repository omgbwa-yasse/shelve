<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {


        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->string('action', 150)->nullable(true);
            $table->text('description');
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('path', 100)->nullable(false);
            $table->string('name', 100)->nullable(false);
            $table->string('crypt', 255)->nullable(false);
            $table->string('thumbnail_path', 150)->nullable(false);
            $table->integer('size')->nullable(false);
            $table->string('crypt_sha512')->nullable(false);
            $table->enum('type', ['mail','record','communication','transferting'])->nullable(false);
            $table->unsignedBigInteger('creator_id')->nullable(false);
            $table->timestamps();
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
        });


        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false)->unique();
            $table->longText('description')->nullable(false);
            $table->timestamps();
        });


        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->unique();
            $table->string('name', 50);
            $table->string('native_name', 50)->nullable();
            $table->text('description')->nullable();
        });

        Schema::create('ladp_servers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->string('ip_address', 45);
            $table->unsignedSmallInteger('port')->nullable();
            $table->enum('status', ['online', 'offline', 'maintenance'])->default('online');
            $table->timestamps();

            $table->unique(['ip_address', 'port']);
        });



        Schema::create('ladp_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->string('ip_address', 45);
            $table->unsignedSmallInteger('port')->nullable();
            $table->foreignId('server_id')->nullable()->constrained('ladp_servers')->nullOnDelete();
            $table->timestamps();

            $table->unique(['ip_address', 'port']);
        });



        Schema::create('ladp_contents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 50)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('hash', 64)->nullable();
            $table->foreignId('server_id')->nullable()->constrained('ladp_servers')->nullOnDelete();
            $table->timestamps();

            $table->unique(['name', 'server_id']);
        });



        Schema::create('ladp_distribution', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('content_id');
            $table->unsignedBigInteger('client_id');
            $table->timestamp('start_time')->useCurrent();
            $table->timestamp('end_time')->nullable(true);
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed'])->default('pending');
            $table->timestamps();

            $table->unique(['content_id', 'client_id']);

            $table->foreign('content_id')->references('id')->on('ladp_contents')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('ladp_clients')->onDelete('cascade');
        });



        /* Sauvegarde */



    }

    public function down()
    {

    }
};
