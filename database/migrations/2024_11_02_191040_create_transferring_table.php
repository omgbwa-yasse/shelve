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
            Transferring archives
        */

        Schema::create('slip_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('slips', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(true)->nullable(false);
            $table->string('name', 200)->nullable(false);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('officer_organisation_id')->nullable(false);
            $table->unsignedBigInteger('officer_id')->nullable(false);
            $table->unsignedBigInteger('user_organisation_id')->nullable(false);
            $table->unsignedBigInteger('user_id')->nullable(true);
            $table->unsignedBigInteger('slip_status_id')->nullable(false);
            $table->boolean('is_received')->nullable(true)->default(false);
            $table->dateTime('received_date')->nullable();
            $table->unsignedBigInteger('received_by')->nullable(true);
            $table->boolean('is_approved')->nullable(true)->default(false);
            $table->dateTime('approved_date')->nullable(true);
            $table->unsignedBigInteger('approved_by')->nullable(true);
            $table->boolean('is_integrated')->nullable(true)->default(false);
            $table->dateTime('integrated_date')->nullable(true);
            $table->unsignedBigInteger('integrated_by')->nullable(true);
            $table->timestamps();
            $table->foreign('officer_organisation_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('officer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_organisation_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('slip_status_id')->references('id')->on('slip_statuses')->onDelete('cascade');
            $table->foreign('received_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('integrated_by')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('slip_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('slip_id')->nullable(false);
            $table->string('code', 10)->nullable(false);
            $table->text('name')->nullable(false);
            $table->string('date_format', 1)->nullable(false);
            $table->string('date_start', 10)->nullable(true);
            $table->string('date_end', 10)->nullable(true);
            $table->date('date_exact')->nullable(true);
            $table->text('content')->nullable(true);
            $table->unsignedBigInteger('level_id')->nullable(false);
            $table->float('width', 10)->nullable(true);
            $table->string('width_description', 100)->nullable(true);
            $table->unsignedBigInteger('support_id')->nullable(false);
            $table->unsignedBigInteger('activity_id')->nullable(false);
            $table->unsignedBigInteger('creator_id')->nullable(false);
            $table->timestamps();
            $table->foreign('slip_id')->references('id')->on('slips')->onDelete('cascade');
            $table->foreign('level_id')->references('id')->on('levels')->onDelete('cascade'); // Ajout de la clé étrangère manquante
            $table->foreign('support_id')->references('id')->on('record_supports')->onDelete('cascade');
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('slip_record_container', function (Blueprint $table) {
            $table->unsignedBigInteger('slip_record_id')->nullable(false);
            $table->unsignedBigInteger('container_id')->nullable(false);
            $table->unsignedBigInteger('creator_id')->nullable(false);
            $table->string('description', 200)->nullable(false);
            $table->primary(['slip_record_id', 'container_id']);
            $table->timestamps();
            $table->foreign('slip_record_id')->references('id')->on('slip_records')->onDelete('cascade'); // Correction de slip_id en slip_record_id
            $table->foreign('container_id')->references('id')->on('containers')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('slip_record_attachment', function (Blueprint $table) {
            $table->unsignedBigInteger('slip_record_id')->nullable(false);
            $table->unsignedBigInteger('attachment_id')->nullable(false);
            $table->timestamps();
            $table->primary(['slip_record_id', 'attachment_id']);
            $table->foreign('slip_record_id')->references('id')->on('slip_records')->onDelete('cascade');
            $table->foreign('attachment_id')->references('id')->on('attachments')->onDelete('cascade');
        });

        Schema::create('slip_attachment', function (Blueprint $table) {
            $table->unsignedBigInteger('slip_id')->nullable(false);
            $table->unsignedBigInteger('attachment_id')->nullable(false);
            $table->timestamps();
            $table->primary(['slip_id', 'attachment_id']);
            $table->foreign('slip_id')->references('id')->on('slips')->onDelete('cascade'); // Correction de slip_record_id en slip_id
            $table->foreign('attachment_id')->references('id')->on('attachments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slip_attachment');
        Schema::dropIfExists('slip_record_attachment');
        Schema::dropIfExists('slip_record_container');
        Schema::dropIfExists('slip_records');
        Schema::dropIfExists('slips');
        Schema::dropIfExists('slip_statuses');
    }
};
