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
        // Table des types de dossiers numériques
        Schema::create('record_digital_folder_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Code technique : CONTRACTS, HR, PROJECTS');
            $table->string('name', 200)->comment('Nom du type');
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable()->comment('Icône FontAwesome');
            $table->string('color', 7)->nullable()->comment('Code couleur hexa');

            // Relation avec les templates de métadonnées
            $table->unsignedBigInteger('metadata_template_id')->nullable();
            $table->foreign('metadata_template_id')
                ->references('id')
                ->on('metadata_templates')
                ->onDelete('set null');

            // Configuration du code généré
            $table->string('code_prefix', 10)->nullable()->comment('Préfixe du code : CTR, HR, PRJ');
            $table->string('code_pattern', 100)->default('{{PREFIX}}-{{YEAR}}-{{SEQ}}');

            // Règles métier
            $table->enum('default_access_level', ['public', 'internal', 'restricted', 'confidential', 'secret'])
                ->default('internal');
            $table->boolean('requires_approval')->default(false)->comment('Nécessite une approbation');
            $table->json('mandatory_metadata')->nullable()->comment('Métadonnées obligatoires');
            $table->json('allowed_document_types')->nullable()->comment('Types de documents autorisés');

            // Système
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false)->comment('Type système non modifiable');
            $table->integer('display_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('code');
            $table->index('is_active');
            $table->index('display_order');
        });

        // Table des types de documents numériques
        Schema::create('record_digital_document_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Code technique : INVOICE, QUOTE, CONTRACT_DOC');
            $table->string('name', 200)->comment('Nom du type');
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable();
            $table->string('color', 7)->nullable();

            // Relation avec les templates de métadonnées
            $table->unsignedBigInteger('metadata_template_id')->nullable();
            $table->foreign('metadata_template_id')
                ->references('id')
                ->on('metadata_templates')
                ->onDelete('set null');

            // Configuration du code généré
            $table->string('code_prefix', 10)->nullable();
            $table->string('code_pattern', 100)->default('{{PREFIX}}-{{YEAR}}-{{SEQ}}');

            // Règles métier
            $table->enum('default_access_level', ['public', 'internal', 'restricted', 'confidential', 'secret'])
                ->default('internal');
            $table->json('allowed_mime_types')->nullable()->comment('Types MIME autorisés : ["application/pdf"]');
            $table->json('allowed_extensions')->nullable()->comment('Extensions autorisées : [".pdf", ".docx"]');
            $table->bigInteger('max_file_size')->nullable()->comment('Taille max en octets');
            $table->boolean('requires_signature')->default(false);
            $table->boolean('requires_approval')->default(false);
            $table->json('mandatory_metadata')->nullable();
            $table->integer('retention_years')->nullable()->comment('Durée de conservation en années');

            // Versioning
            $table->boolean('enable_versioning')->default(true);
            $table->integer('max_versions')->nullable()->comment('Nombre max de versions conservées');

            // Système
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->integer('display_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('code');
            $table->index('is_active');
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_digital_document_types');
        Schema::dropIfExists('record_digital_folder_types');
    }
};
