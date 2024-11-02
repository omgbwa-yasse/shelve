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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable(false)->unique();
            $table->string('name', 250)->nullable(false);
            $table->unsignedInteger('organisation_holder_id')->nullable(false);
            $table->foreign('organisation_holder_id')->references('id')->on('batches')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('batch_mail', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('batch_id')->nullable(true);
            $table->unsignedBigInteger('mail_id')->nullable(true);
            $table->dateTime('insert_date')->nullable(true);
            $table->dateTime('remove_date')->nullable(true);
            $table->timestamps();
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            $table->foreign('mail_id')->references('id')->on('mails')->onDelete('cascade');
        });

        Schema::create('batch_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('batch_id')->nullable(false);
            $table->unsignedBigInteger('organisation_send_id')->nullable(false);
            $table->unsignedBigInteger('organisation_received_id')->nullable(false);
            $table->timestamps();
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            $table->foreign('organisation_send_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('organisation_received_id')->references('id')->on('organisations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
        Schema::dropIfExists('batch_mail');
        Schema::dropIfExists('batch_transactions');
    }
};
