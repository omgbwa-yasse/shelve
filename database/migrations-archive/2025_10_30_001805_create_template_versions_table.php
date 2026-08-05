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
        Schema::create('template_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id');
            $table->string('version', 10)->comment('Numéro de version');
            $table->longText('layout')->comment('Structure HTML de cette version');
            $table->longText('custom_css')->nullable()->comment('CSS de cette version');
            $table->longText('custom_js')->nullable()->comment('JavaScript de cette version');
            $table->json('variables')->nullable()->comment('Variables de cette version');
            $table->json('components')->nullable()->comment('Composants de cette version');
            $table->json('meta')->nullable()->comment('Métadonnées de cette version');
            $table->string('created_by')->comment('Utilisateur ayant créé cette version');
            $table->text('change_description')->nullable()->comment('Description des changements');
            $table->boolean('is_active')->default(false)->comment('Version actuellement active');
            $table->timestamps();

            $table->foreign('template_id')->references('id')->on('templates')->onDelete('cascade');
            $table->unique(['template_id', 'version'], 'template_version_unique');
            $table->index(['template_id', 'is_active'], 'template_active_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_versions');
    }
};
