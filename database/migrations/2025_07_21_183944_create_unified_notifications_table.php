<?php

use App\Enums\NotificationModule;
use App\Enums\NotificationPriority;
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
        // Supprimer les anciennes tables
        Schema::dropIfExists('mail_notifications');
        Schema::dropIfExists('system_notifications');

        // Créer la nouvelle table unifiée
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('organisation_id')->nullable()->constrained()->onDelete('cascade');

            // Module concerné par la notification
            $table->enum('module', array_column(NotificationModule::cases(), 'value'))->index();

            // Type d'événement (ex: post_created, mail_received, etc.)
            $table->string('event_type')->index();

            // Contenu de la notification
            $table->string('title');
            $table->text('message');

            // Priorité
            $table->enum('priority', array_column(NotificationPriority::cases(), 'value'))->default('medium');

            // Données additionnelles en JSON
            $table->json('data')->nullable();

            // URL d'action optionnelle
            $table->string('action_url')->nullable();

            // État de lecture
            $table->timestamp('read_at')->nullable()->index();

            // Planification optionnelle
            $table->timestamp('scheduled_for')->nullable()->index();

            $table->timestamps();

            // Index composites pour optimiser les requêtes
            $table->index(['user_id', 'read_at']);
            $table->index(['organisation_id', 'read_at']);
            $table->index(['module', 'event_type']);
            $table->index(['created_at', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
