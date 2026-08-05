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
            Déclassement / élimination + réactivation de dossier
        */

        Schema::create('declassement_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('declassement_lists', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(true)->nullable(false);
            $table->string('name', 200)->nullable(false);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('organisation_id')->nullable(false);
            $table->unsignedBigInteger('declassement_status_id')->nullable(false);
            $table->json('query_criteria')->nullable();
            $table->unsignedBigInteger('creator_id')->nullable(false);
            $table->boolean('is_approval_requested')->nullable(true)->default(false);
            $table->dateTime('approval_requested_date')->nullable();
            $table->unsignedBigInteger('approval_requested_by')->nullable(true);
            $table->boolean('is_approved')->nullable(true)->default(false);
            $table->dateTime('approved_date')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable(true);
            $table->boolean('is_validated')->nullable(true)->default(false);
            $table->dateTime('validated_date')->nullable();
            $table->unsignedBigInteger('validated_by')->nullable(true);
            $table->boolean('is_treated')->nullable(true)->default(false);
            $table->dateTime('treated_date')->nullable();
            $table->unsignedBigInteger('treated_by')->nullable(true);
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('declassement_status_id')->references('id')->on('declassement_statuses')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approval_requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('validated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('treated_by')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('declassement_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('declassement_list_id')->nullable(false);
            $table->unsignedBigInteger('record_physical_id')->nullable(false);
            $table->unsignedBigInteger('added_by')->nullable(false);
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['declassement_list_id', 'record_physical_id'], 'declassement_list_record_unique');
            $table->foreign('declassement_list_id')->references('id')->on('declassement_lists')->onDelete('cascade');
            $table->foreign('record_physical_id')->references('id')->on('record_physicals')->onDelete('cascade');
            $table->foreign('added_by')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('declassement_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('declassement_list_id')->nullable(false);
            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->text('content')->nullable(false);
            $table->timestamps();

            $table->foreign('declassement_list_id')->references('id')->on('declassement_lists')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('record_reactivations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('record_physical_id')->nullable(false);
            $table->unsignedBigInteger('organisation_id')->nullable(false);
            $table->unsignedBigInteger('previous_status_id')->nullable(true);
            $table->date('previous_transfer_date')->nullable();
            $table->date('new_transfer_date')->nullable();
            $table->text('reason')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->boolean('is_approved')->nullable(true)->default(false);
            $table->unsignedBigInteger('requested_by')->nullable(false);
            $table->dateTime('requested_date')->nullable(false);
            $table->unsignedBigInteger('approved_by')->nullable(true);
            $table->dateTime('approved_date')->nullable();
            $table->timestamps();

            $table->foreign('record_physical_id')->references('id')->on('record_physicals')->onDelete('cascade');
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('previous_status_id')->references('id')->on('record_statuses')->onDelete('set null');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_reactivations');
        Schema::dropIfExists('declassement_comments');
        Schema::dropIfExists('declassement_records');
        Schema::dropIfExists('declassement_lists');
        Schema::dropIfExists('declassement_statuses');
    }
};
