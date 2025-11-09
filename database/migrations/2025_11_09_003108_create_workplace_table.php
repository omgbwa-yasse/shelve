<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * WorkPlace: Espaces de travail collaboratifs pour la création et le partage de records
     */
    public function up(): void
    {
        // Table des catégories d'espaces de travail (doit être créée EN PREMIER)
        Schema::create('workplace_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Code unique (HR, FINANCE, IT, etc.)');
            $table->string('name')->comment('Nom de la catégorie');
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable()->comment('Icône FontAwesome');
            $table->string('color', 7)->nullable()->comment('Couleur hexa');
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('display_order');
        });

        // Table principale des espaces de travail (WorkPlace)
        Schema::create('workplaces', function (Blueprint $table) {
            $table->id();

            // Identification
            $table->string('code', 50)->unique()->comment('Code unique : WP-YYYY-NNNN');
            $table->string('name')->comment('Nom de l\'espace de travail');
            $table->text('description')->nullable()->comment('Description et objectifs');

            $table->foreignId('category_id')->nullable()->constrained('workplace_categories')->onDelete('set null')->comment('Catégorie (RH, Finance, IT, etc.)');

            // Configuration visuelle
            $table->string('icon', 50)->nullable()->comment('Icône FontAwesome');
            $table->string('color', 7)->default('#3498db')->comment('Couleur hexa pour UI');

            // Paramètres de l'espace
            $table->json('settings')->nullable()->comment('Configuration JSON (notifications, permissions, etc.)');
            $table->boolean('is_public')->default(false)->comment('Visible par tous');
            $table->boolean('allow_external_sharing')->default(false)->comment('Partage externe autorisé');
            $table->integer('max_members')->default(50)->comment('Nombre max de membres');
            $table->integer('max_storage_mb')->default(5120)->comment('Espace de stockage max en MB (5GB par défaut)');

            // Statistiques
            $table->integer('members_count')->default(0)->comment('Nombre de membres');
            $table->integer('folders_count')->default(0)->comment('Nombre de dossiers');
            $table->integer('documents_count')->default(0)->comment('Nombre de documents');
            $table->bigInteger('storage_used_bytes')->default(0)->comment('Stockage utilisé en octets');

            // Statut et dates
            $table->enum('status', ['active', 'archived', 'suspended', 'closed'])
                ->default('active')
                ->comment('Statut de l\'espace');
            $table->date('start_date')->nullable()->comment('Date de début');
            $table->date('end_date')->nullable()->comment('Date de fin prévue');
            $table->timestamp('archived_at')->nullable()->comment('Date d\'archivage');

            // Relations organisationnelles
            $table->foreignId('organisation_id')->constrained('organisations')->comment('Organisation propriétaire');
            $table->foreignId('owner_id')->constrained('users')->comment('Propriétaire de l\'espace');
            $table->foreignId('created_by')->constrained('users')->comment('Créateur');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('category_id');
            $table->index('status');
            $table->index('organisation_id');
            $table->index('owner_id');
            $table->index(['category_id', 'status']);
            $table->index('created_at');
        });


        // Table des membres de l'espace de travail
        Schema::create('workplace_members', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workplace_id')->constrained('workplaces')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Rôle dans l'espace
            $table->enum('role', ['owner', 'admin', 'editor', 'contributor', 'viewer'])
                ->default('contributor')
                ->comment('Rôle du membre dans l\'espace');

            // Permissions spécifiques
            $table->boolean('can_create_folders')->default(true);
            $table->boolean('can_create_documents')->default(true);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_share')->default(true);
            $table->boolean('can_invite')->default(false);

            // Notifications
            $table->boolean('notify_on_new_content')->default(true);
            $table->boolean('notify_on_mentions')->default(true);
            $table->boolean('notify_on_updates')->default(false);

            // Audit
            $table->foreignId('invited_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('last_activity_at')->nullable()->comment('Dernière activité dans l\'espace');

            $table->timestamps();

            // Contraintes
            $table->unique(['workplace_id', 'user_id']);
            $table->index('role');
            $table->index('joined_at');
        });



        // Table de liaison: WorkPlace -> Digital Folders
        Schema::create('workplace_folders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workplace_id')->constrained('workplaces')->onDelete('cascade');
            $table->foreignId('folder_id')->constrained('record_digital_folders')->onDelete('cascade');

            // Métadonnées de partage
            $table->foreignId('shared_by')->constrained('users')->comment('Partagé par');
            $table->timestamp('shared_at')->useCurrent()->comment('Date de partage');
            $table->text('share_note')->nullable()->comment('Note de partage');

            // Permissions sur le dossier partagé
            $table->enum('access_level', ['view', 'edit', 'full'])
                ->default('view')
                ->comment('Niveau d\'accès pour les membres');
            $table->boolean('is_pinned')->default(false)->comment('Épinglé en haut');
            $table->integer('display_order')->default(0)->comment('Ordre d\'affichage');

            $table->timestamps();

            // Contraintes
            $table->unique(['workplace_id', 'folder_id']);
            $table->index('shared_by');
            $table->index('is_pinned');
        });



        // Table de liaison: WorkPlace -> Digital Documents
        Schema::create('workplace_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workplace_id')->constrained('workplaces')->onDelete('cascade');
            $table->foreignId('document_id')->constrained('record_digital_documents')->onDelete('cascade');

            // Métadonnées de partage
            $table->foreignId('shared_by')->constrained('users')->comment('Partagé par');
            $table->timestamp('shared_at')->useCurrent()->comment('Date de partage');
            $table->text('share_note')->nullable()->comment('Note de partage');

            // Permissions sur le document partagé
            $table->enum('access_level', ['view', 'edit', 'full'])
                ->default('view')
                ->comment('Niveau d\'accès pour les membres');
            $table->boolean('is_featured')->default(false)->comment('Document mis en avant');
            $table->integer('views_count')->default(0)->comment('Nombre de vues');
            $table->timestamp('last_viewed_at')->nullable()->comment('Dernière consultation');

            $table->timestamps();

            // Contraintes
            $table->unique(['workplace_id', 'document_id']);
            $table->index('shared_by');
            $table->index('is_featured');
        });




        // Table des activités dans l'espace de travail
        Schema::create('workplace_activities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workplace_id')->constrained('workplaces')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Type d'activité
            $table->enum('activity_type', [
                'created_folder',
                'created_document',
                'shared_folder',
                'shared_document',
                'updated_folder',
                'updated_document',
                'deleted_folder',
                'deleted_document',
                'joined',
                'left',
                'member_added',
                'member_removed',
                'settings_changed'
            ])->comment('Type d\'activité');

            // Détails de l'activité
            $table->string('subject_type')->nullable()->comment('Type d\'entité concernée (polymorphic)');
            $table->unsignedBigInteger('subject_id')->nullable()->comment('ID de l\'entité concernée');
            $table->text('description')->nullable()->comment('Description de l\'activité');
            $table->json('metadata')->nullable()->comment('Données supplémentaires (ancien/nouveau, etc.)');

            // IP et contexte
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('activity_type');
            $table->index('created_at');
            $table->index(['subject_type', 'subject_id']);
        });



        // Table des invitations en attente
        Schema::create('workplace_invitations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workplace_id')->constrained('workplaces')->onDelete('cascade');
            $table->foreignId('invited_by')->constrained('users')->onDelete('cascade');

            // Destinataire (peut ne pas encore être utilisateur)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('email')->nullable()->comment('Email si utilisateur externe');

            // Configuration de l'invitation
            $table->enum('proposed_role', ['admin', 'editor', 'contributor', 'viewer'])->default('contributor');
            $table->text('message')->nullable()->comment('Message d\'invitation personnalisé');
            $table->string('token', 64)->unique()->comment('Token unique pour accepter l\'invitation');

            // Statut
            $table->enum('status', ['pending', 'accepted', 'declined', 'expired'])->default('pending');
            $table->timestamp('expires_at')->comment('Date d\'expiration');
            $table->timestamp('responded_at')->nullable()->comment('Date de réponse');

            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('token');
            $table->index('expires_at');
        });



        // Table des favoris/signets
        Schema::create('workplace_bookmarks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workplace_id')->constrained('workplaces')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Entité mise en favori (polymorphic)
            $table->string('bookmarkable_type')->comment('Type: RecordDigitalFolder, RecordDigitalDocument');
            $table->unsignedBigInteger('bookmarkable_id');

            $table->text('note')->nullable()->comment('Note personnelle');
            $table->string('tags')->nullable()->comment('Tags personnels séparés par des virgules');

            $table->timestamps();

            // Contraintes
            $table->unique(['workplace_id', 'user_id', 'bookmarkable_type', 'bookmarkable_id'], 'unique_bookmark');
            $table->index(['bookmarkable_type', 'bookmarkable_id']);
        });




        // Table des templates d'espace de travail
        Schema::create('workplace_templates', function (Blueprint $table) {
            $table->id();

            $table->string('code', 50)->unique()->comment('Code unique du template');
            $table->string('name')->comment('Nom du template');
            $table->text('description')->nullable();

            // Configuration
            $table->string('icon', 50)->nullable();
            $table->string('category')->nullable()->comment('Catégorie de template');
            $table->json('default_settings')->nullable()->comment('Paramètres par défaut');
            $table->json('default_structure')->nullable()->comment('Structure de dossiers par défaut');
            $table->json('default_permissions')->nullable()->comment('Permissions par défaut');

            // Métadonnées
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false)->comment('Template système non modifiable');
            $table->integer('usage_count')->default(0)->comment('Nombre d\'utilisations');
            $table->integer('display_order')->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('code');
            $table->index('category');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workplace_templates');
        Schema::dropIfExists('workplace_bookmarks');
        Schema::dropIfExists('workplace_invitations');
        Schema::dropIfExists('workplace_activities');
        Schema::dropIfExists('workplace_documents');
        Schema::dropIfExists('workplace_folders');
        Schema::dropIfExists('workplace_members');
        Schema::dropIfExists('workplaces');
        Schema::dropIfExists('workplace_categories');
    }
};
