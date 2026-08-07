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
        Schema::table('notifications', function (Blueprint $table) {
            // Ajouter le module enum pour les 13 modules du menu
            $table->enum('module', [
                'BULLETIN_BOARDS',
                'MAILS',
                'RECORDS',
                'COMMUNICATIONS',
                'TRANSFERS',
                'DEPOSITS',
                'TOOLS',
                'DOLLIES',
                'WORKFLOWS',
                'CONTACTS',
                'AI',
                'PUBLIC',
                'SETTINGS'
            ])->default('BULLETIN_BOARDS')->after('id');

            // Ajouter les champs nécessaires s'ils n'existent pas déjà
            if (!Schema::hasColumn('notifications', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('module');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }

            if (!Schema::hasColumn('notifications', 'organisation_id')) {
                $table->unsignedBigInteger('organisation_id')->nullable()->after('user_id');
                $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
            }

            if (!Schema::hasColumn('notifications', 'title')) {
                $table->string('title')->after('organisation_id');
            }

            if (!Schema::hasColumn('notifications', 'message')) {
                $table->text('message')->after('title');
            }

            if (!Schema::hasColumn('notifications', 'priority')) {
                $table->enum('priority', ['LOW', 'MEDIUM', 'HIGH'])->default('MEDIUM')->after('message');
            }

            if (!Schema::hasColumn('notifications', 'is_read')) {
                $table->boolean('is_read')->default(false)->after('priority');
            }

            if (!Schema::hasColumn('notifications', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('is_read');
            }

            if (!Schema::hasColumn('notifications', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('read_at');
            }

            if (!Schema::hasColumn('notifications', 'metadata')) {
                $table->json('metadata')->nullable()->after('scheduled_at');
            }

            // Mettre à jour les timestamps si nécessaire
            if (!Schema::hasColumn('notifications', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Supprimer les colonnes ajoutées
            $table->dropColumn([
                'module',
                'metadata',
                'scheduled_at',
                'read_at',
                'is_read',
                'priority'
            ]);

            // Supprimer les foreign keys et colonnes si elles ont été ajoutées
            if (Schema::hasColumn('notifications', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }

            if (Schema::hasColumn('notifications', 'organisation_id')) {
                $table->dropForeign(['organisation_id']);
                $table->dropColumn('organisation_id');
            }

            if (Schema::hasColumn('notifications', 'title')) {
                $table->dropColumn('title');
            }

            if (Schema::hasColumn('notifications', 'message')) {
                $table->dropColumn('message');
            }
        });
    }
};
