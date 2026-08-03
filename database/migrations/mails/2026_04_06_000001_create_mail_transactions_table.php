<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mail_transactions')) {
            Schema::create('mail_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->nullable();
                $table->datetime('date_creation')->nullable();
                $table->unsignedBigInteger('mail_id')->nullable();
                $table->unsignedBigInteger('user_send_id')->nullable();
                $table->unsignedBigInteger('organisation_send_id')->nullable();
                $table->unsignedBigInteger('user_received_id')->nullable();
                $table->unsignedBigInteger('organisation_received_id')->nullable();
                $table->unsignedBigInteger('mail_type_id')->nullable();
                $table->unsignedBigInteger('document_type_id')->nullable();
                $table->unsignedBigInteger('action_id')->nullable();
                $table->boolean('to_return')->default(false)->nullable();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('batch_id')->nullable();
                $table->timestamps();

                $table->foreign('mail_id')->references('id')->on('mails')->onDelete('cascade');
                $table->foreign('user_send_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('organisation_send_id')->references('id')->on('organisations')->onDelete('cascade');
                $table->foreign('user_received_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('organisation_received_id')->references('id')->on('organisations')->onDelete('cascade');
                $table->foreign('batch_id')->references('id')->on('batches')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_transactions');
    }
};
