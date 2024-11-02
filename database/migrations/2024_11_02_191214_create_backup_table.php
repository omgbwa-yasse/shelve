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

        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->timestamp('date_time')->useCurrent()->nullable(false);
            $table->enum('type', ['metadata', 'full'])->nullable(false);
            $table->text('description')->nullable(true);
            $table->enum('status', ['in_progress', 'success', 'failed'])->nullable(false);
            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->bigInteger('size')->nullable(false);
            $table->string('backup_file')->nullable(false);
            $table->string('path')->nullable(false);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('backup_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('backup_id')->nullable(false);
            $table->string('path_original')->nullable(false);
            $table->string('path_storage')->nullable(false);
            $table->bigInteger('size')->nullable(false);
            $table->string('hash', 150)->nullable(false);
            $table->timestamps();
            $table->foreign('backup_id')->references('id')->on('backups')->onDelete('cascade');
        });

        Schema::create('backup_plannings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('backup_id')->nullable(false);
            $table->string('frequence')->nullable(false);
            $table->integer('week_day')->nullable(true);
            $table->integer('month_day')->nullable(true);
            $table->time('hour')->nullable(true);
            $table->timestamps();
            $table->foreign('backup_id')->references('id')->on('backups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_');
    }
};
