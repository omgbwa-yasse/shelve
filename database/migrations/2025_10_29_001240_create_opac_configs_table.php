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
        Schema::create('opac_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->nullable()->constrained()->onDelete('cascade');

            // Organisations visibles dans l'OPAC
            $table->json('visible_organisations')->nullable();

            // Configuration de l'affichage
            $table->boolean('show_statistics')->default(true);
            $table->boolean('show_recent_records')->default(true);
            $table->boolean('show_full_record_details')->default(true);
            $table->boolean('show_attachments')->default(false);

            // Configuration des téléchargements
            $table->boolean('allow_downloads')->default(false);
            $table->json('allowed_file_types')->nullable();

            // Configuration de la recherche
            $table->boolean('enable_advanced_search')->default(true);
            $table->boolean('show_activity_filter')->default(true);
            $table->boolean('show_date_filter')->default(true);
            $table->boolean('show_author_filter')->default(true);
            $table->integer('records_per_page')->default(20);
            $table->integer('max_search_results')->default(1000);

            // Informations de contact
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('contact_address')->nullable();

            // Personnalisation du site
            $table->string('site_title')->default('OPAC - Online Public Access Catalog');
            $table->text('site_description')->nullable();
            $table->string('logo_path')->nullable();
            $table->text('custom_css')->nullable();
            $table->text('footer_text')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opac_configs');
    }
};
