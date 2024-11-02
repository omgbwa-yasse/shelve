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

        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique()->nullable(false);
            $table->string('name', 200)->nullable(false); // Nouvellement ajoutée
            $table->text('content')->nullable(true); // Nouvellement ajoutée
            $table->unsignedBigInteger('operator_id')->nullable(false);
            $table->unsignedBigInteger('operator_organisation_id')->nullable(false);
            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->unsignedBigInteger('user_organisation_id')->nullable(false);
            $table->date('return_date')->nullable(false);
            $table->date('return_effective')->nullable();
            $table->unsignedBigInteger('status_id')->nullable(false);
            $table->timestamps();
            $table->foreign('operator_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('status_id')->references('id')->on('communication_statuses')->onDelete('cascade');
            $table->foreign('user_organisation_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('operator_organisation_id')->references('id')->on('organisations')->onDelete('cascade');
        });

        Schema::create('communication_record', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('communication_id')->nullable(false);
            $table->unsignedBigInteger('record_id')->nullable(false);
            $table->text('content')->nullable(true);
            $table->boolean('is_original')->default(false)->nullable(false);
            $table->date('return_date')->nullable(false);
            $table->date('return_effective')->nullable();
            $table->unsignedBigInteger('operator_id')->nullable(false);
            $table->timestamps();
            $table->foreign('communication_id')->references('id')->on('communications')->onDelete('cascade');
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
            $table->foreign('operator_id')->references('id')->on('users')->onDelete('cascade');
        });



        Schema::create('communication_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->nullable(false);
            $table->text('description')->nullable(true);
            $table->timestamps();
        });

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique()->nullable(false);
            $table->string('name', 200)->nullable(false); // Nouvellement ajoutée
            $table->text('content')->nullable(true); // Nouvellement ajoutée
            $table->unsignedBigInteger('operator_id')->nullable(false);
            $table->unsignedBigInteger('operator_organisation_id')->nullable(false);
            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->unsignedBigInteger('user_organisation_id')->nullable(false);
            $table->unsignedBigInteger('status_id')->nullable(false);
            $table->timestamps();
            $table->foreign('operator_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('status_id')->references('id')->on('reservation_statuses')->onDelete('cascade');
            $table->foreign('user_organisation_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('operator_organisation_id')->references('id')->on('organisations')->onDelete('cascade');
        });

        Schema::create('reservation_record', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reservation_id')->nullable(false);
            $table->unsignedBigInteger('record_id')->nullable(false);
            $table->boolean('is_original')->default(false)->nullable(false);
            $table->date('reservation_date')->nullable(false);
            $table->unsignedBigInteger('operator_id')->nullable(false);
            $table->date('communication_id')->nullable();
            $table->timestamps();
            $table->foreign('communication_id')->references('id')->on('communications')->onDelete('cascade');
            $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('cascade');
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
            $table->foreign('operator_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('reservation_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->nullable(false);
            $table->text('description')->nullable(true);
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communication');
    }
};
