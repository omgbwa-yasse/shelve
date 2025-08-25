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
        Schema::create('system_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version');
            $table->string('previous_version')->nullable();
            $table->timestamp('installed_at');
            $table->json('changelog')->nullable();
            $table->string('installation_method')->default('manual'); // 'github', 'manual', 'auto'
            $table->boolean('is_rollback')->default(false);
            $table->unsignedBigInteger('installed_by')->nullable();
            $table->string('download_url')->nullable();
            $table->string('checksum')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('installed_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['version', 'installed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_versions');
    }
};
