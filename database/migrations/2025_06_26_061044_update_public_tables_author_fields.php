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
        // Mise à jour de la table public_news
        Schema::table('public_news', function (Blueprint $table) {
            $table->renameColumn('user_id', 'author_id');
        });

        // Mise à jour de la table public_pages si elle existe
        if (Schema::hasTable('public_pages')) {
            Schema::table('public_pages', function (Blueprint $table) {
                if (!Schema::hasColumn('public_pages', 'author_id')) {
                    $table->foreignId('author_id')->nullable()->after('id')->constrained('users')->onDelete('set null');
                }
                if (!Schema::hasColumn('public_pages', 'title')) {
                    $table->string('title')->after('author_id');
                }
                if (!Schema::hasColumn('public_pages', 'meta_description')) {
                    $table->text('meta_description')->nullable()->after('content');
                }
                if (!Schema::hasColumn('public_pages', 'meta_keywords')) {
                    $table->string('meta_keywords')->nullable()->after('meta_description');
                }
                if (!Schema::hasColumn('public_pages', 'status')) {
                    $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->after('meta_keywords');
                }
                if (!Schema::hasColumn('public_pages', 'featured_image_path')) {
                    $table->string('featured_image_path')->nullable()->after('status');
                }
            });
        }

        // Mise à jour de la table public_templates si elle existe
        if (Schema::hasTable('public_templates')) {
            Schema::table('public_templates', function (Blueprint $table) {
                if (!Schema::hasColumn('public_templates', 'author_id')) {
                    $table->foreignId('author_id')->nullable()->after('id')->constrained('users')->onDelete('set null');
                }
                if (!Schema::hasColumn('public_templates', 'type')) {
                    $table->enum('type', ['page', 'email', 'notification'])->default('page')->after('description');
                }
                if (!Schema::hasColumn('public_templates', 'content')) {
                    $table->text('content')->after('type');
                }
                if (!Schema::hasColumn('public_templates', 'variables')) {
                    $table->json('variables')->nullable()->after('content');
                }
                if (!Schema::hasColumn('public_templates', 'status')) {
                    $table->enum('status', ['active', 'inactive'])->default('active')->after('variables');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback pour public_news
        Schema::table('public_news', function (Blueprint $table) {
            $table->renameColumn('author_id', 'user_id');
        });

        // Rollback pour public_pages
        if (Schema::hasTable('public_pages')) {
            Schema::table('public_pages', function (Blueprint $table) {
                $table->dropColumn(['author_id', 'title', 'meta_description', 'meta_keywords', 'status', 'featured_image_path']);
            });
        }

        // Rollback pour public_templates
        if (Schema::hasTable('public_templates')) {
            Schema::table('public_templates', function (Blueprint $table) {
                $table->dropColumn(['author_id', 'type', 'content', 'variables', 'status']);
            });
        }
    }
};
