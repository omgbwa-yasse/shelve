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
            $table->string('code', 50)->unique()->comment('Code unique du type (ex: CONTRACTS, HR, INVOICES)');
            $table->string('name')->comment('Nom du type de dossier');
            $table->text('description')->nullable()->comment('Description détaillée du type');

            // Configuration
            $table->string('code_pattern')->nullable()->comment('Pattern de génération de code (ex: CTR-{YYYY}-{NNN})');
            $table->integer('max_depth')->default(5)->comment('Profondeur maximale de hiérarchie autorisée');
            $table->boolean('allows_documents')->default(true)->comment('Autorise les documents dans ce type de dossier');
            $table->json('allowed_document_types')->nullable()->comment('Types de documents autorisés (IDs)');

            // Métadonnées et template
            $table->unsignedBigInteger('metadata_template_id')->nullable();
            // $table->foreign('metadata_template_id')->references('id')->on('metadata_templates')->onDelete('set null');
            $table->json('required_metadata_fields')->nullable()->comment('Champs de métadonnées obligatoires');

            // Règles de nommage et validation
            $table->string('naming_convention')->nullable()->comment('Convention de nommage des dossiers');
            $table->json('validation_rules')->nullable()->comment('Règles de validation personnalisées');

            // Sécurité et workflow
            $table->string('default_access_level')->default('public')->comment('Niveau d\'accès par défaut');
            $table->boolean('requires_approval')->default(false)->comment('Nécessite approbation');
            $table->boolean('version_control')->default(false)->comment('Contrôle de version activé');

            // Rétention
            $table->integer('retention_years')->nullable()->comment('Durée de conservation en années');
            $table->string('retention_policy')->nullable()->comment('Politique de conservation');

            // Statistiques
            $table->integer('folders_count')->default(0)->comment('Nombre de dossiers de ce type');
            $table->timestamp('last_used_at')->nullable()->comment('Dernière utilisation');

            // État et organisation
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->string('icon')->nullable()->comment('Icône pour l\'interface');
            $table->string('color')->nullable()->comment('Couleur pour l\'interface');

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('code');
            $table->index('is_active');
            $table->index('display_order');
        });

        // Table des types de documents numériques
        Schema::create('record_digital_document_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Code unique du type (ex: INVOICE, CONTRACT, REPORT)');
            $table->string('name')->comment('Nom du type de document');
            $table->text('description')->nullable()->comment('Description détaillée du type');

            // Catégorie et classification
            $table->string('category')->nullable()->comment('Catégorie (administratif, financier, RH, etc.)');
            $table->json('tags')->nullable()->comment('Tags pour recherche et organisation');

            // Contraintes de fichiers
            $table->json('allowed_mime_types')->nullable()->comment('Types MIME autorisés (application/pdf, image/*, etc.)');
            $table->json('allowed_extensions')->nullable()->comment('Extensions autorisées (pdf, docx, xlsx, etc.)');
            $table->bigInteger('max_file_size')->nullable()->comment('Taille max en octets');
            $table->bigInteger('min_file_size')->nullable()->comment('Taille min en octets');

            // Métadonnées et template
            $table->unsignedBigInteger('metadata_template_id')->nullable();
            // $table->foreign('metadata_template_id')->references('id')->on('metadata_templates')->onDelete('set null');
            $table->json('required_metadata_fields')->nullable()->comment('Champs de métadonnées obligatoires');
            $table->json('optional_metadata_fields')->nullable()->comment('Champs de métadonnées optionnels');

            // Règles de nommage
            $table->string('naming_pattern')->nullable()->comment('Pattern de nommage (ex: {TYPE}-{DATE}-{NNN})');
            $table->json('validation_rules')->nullable()->comment('Règles de validation personnalisées');

            // Versioning et workflow
            $table->boolean('requires_versioning')->default(false)->comment('Versioning obligatoire');
            $table->integer('max_versions')->nullable()->comment('Nombre max de versions conservées');
            $table->boolean('requires_approval')->default(false)->comment('Nécessite approbation');
            $table->boolean('requires_signature')->default(false)->comment('Nécessite signature électronique');

            // Sécurité
            $table->string('default_access_level')->default('public')->comment('Niveau d\'accès par défaut');
            $table->boolean('requires_encryption')->default(false)->comment('Chiffrement obligatoire');
            $table->boolean('watermark_enabled')->default(false)->comment('Filigrane activé');

            // Rétention et archivage
            $table->integer('retention_years')->nullable()->comment('Durée de conservation en années');
            $table->string('retention_policy')->nullable()->comment('Politique de conservation');
            $table->boolean('auto_archive')->default(false)->comment('Archivage automatique');
            $table->integer('archive_after_days')->nullable()->comment('Archiver après X jours');

            // OCR et traitement
            $table->boolean('ocr_enabled')->default(false)->comment('OCR automatique activé');
            $table->boolean('thumbnail_enabled')->default(true)->comment('Génération de miniature');
            $table->boolean('preview_enabled')->default(true)->comment('Prévisualisation activée');

            // Statistiques
            $table->integer('documents_count')->default(0)->comment('Nombre de documents de ce type');
            $table->bigInteger('total_size')->default(0)->comment('Taille totale en octets');
            $table->timestamp('last_used_at')->nullable()->comment('Dernière utilisation');

            // État et organisation
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->string('icon')->nullable()->comment('Icône pour l\'interface');
            $table->string('color')->nullable()->comment('Couleur pour l\'interface');

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('code');
            $table->index('category');
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
