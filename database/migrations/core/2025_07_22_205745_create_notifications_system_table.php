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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('organisation_id')->nullable();
            $table->enum('module', [
                'BulletinBoards',
                'Mails',
                'Records',
                'Communications',
                'Transfers',
                'Deposits',
                'Tools',
                'Dollies',
                'Workflows',
                'Contacts',
                'AI',
                'Public',
                'Settings'
            ]);
            $table->string('name');
            $table->text('message')->nullable();
            $table->enum('action', ['CREATE', 'READ', 'UPDATE', 'DELETE']);
            $table->string('related_entity_type', 50)->nullable();
            $table->unsignedBigInteger('related_entity_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            // Index pour optimiser les performances
            $table->index(['user_id', 'is_read']);
            $table->index(['organisation_id', 'is_read']);
            $table->index(['module', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['related_entity_type', 'related_entity_id']);
            $table->index(['user_id', 'organisation_id', 'is_read']);

            // Clés étrangères
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
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
