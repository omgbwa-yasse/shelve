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
        Schema::create('template_preview_cache', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id');
            $table->string('cache_key', 64)->unique()->comment('Clé de cache MD5');
            $table->string('device_type', 20)->default('desktop')->comment('desktop, tablet, mobile');
            $table->longText('rendered_html')->comment('HTML généré');
            $table->text('css_compiled')->nullable()->comment('CSS compilé');
            $table->json('variables_used')->nullable()->comment('Variables utilisées pour ce rendu');
            $table->integer('file_size')->comment('Taille du HTML généré en octets');
            $table->timestamp('expires_at')->comment('Date d\'expiration du cache');
            $table->timestamps();

            $table->foreign('template_id')->references('id')->on('templates')->onDelete('cascade');
            $table->index(['template_id', 'device_type'], 'template_device_cache');
            $table->index('expires_at', 'cache_expiration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_preview_cache');
    }
};
