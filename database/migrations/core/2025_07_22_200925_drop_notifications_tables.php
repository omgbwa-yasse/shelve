<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fait table rase des notifications historiques ; la table définitive est
     * (re)créée juste après par 2025_07_22_205745_create_notifications_system_table.
     *
     * Les 5 migrations qui construisaient l'ancienne table `notifications`
     * (2025_07_01_222710 / 222801 / 223711 et 2025_07_21_184109 / 185952) ont été
     * retirées du dépôt : elles ne faisaient que bâtir une table supprimée ici même.
     * L'état final de la base est strictement identique, sur une base neuve comme
     * sur une base déjà migrée (leurs lignes restent dans le journal `migrations`,
     * sans fichier — ce que Laravel ignore sans erreur).
     */
    public function up(): void
    {
        // Supprimer les tables de notifications dans l'ordre inverse des dépendances
        Schema::dropIfExists('mail_notifications');
        Schema::dropIfExists('system_notifications');
        Schema::dropIfExists('notifications');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recréer la table notifications principale
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Recréer la table system_notifications
        Schema::create('system_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('info'); // info, warning, error, success
            $table->string('priority')->default('normal'); // low, normal, high
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });

        // Recréer la table mail_notifications
        Schema::create('mail_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('notifiable'); // mail_id et type
            $table->string('type'); // type de notification
            $table->string('title');
            $table->text('message');
            $table->string('priority')->default('normal');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->json('data')->nullable(); // données supplémentaires
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
            $table->index(['type', 'created_at']);
        });
    }
};
