<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('first_name');
            $table->string('phone1');
            $table->string('phone2');
            $table->string('address');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_approved')->default(false);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            $table->index('email');
            $table->index('is_approved');
        });

        Schema::create('public_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->json('parameters');
            $table->json('values');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index('is_active');
        });

        Schema::create('public_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('record_id')->constrained('records')->nullable(false);
            $table->dateTime('published_at');
            $table->dateTime('expires_at')->nullable(true);
            $table->foreignId('published_by')->constrained('users')->nullable(false);
            $table->text('publication_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('record_id');
            $table->index('published_by');
            $table->index('published_at');
            $table->index('expires_at');
        });

        Schema::create('public_events', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->text('description');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('location')->nullable();
            $table->boolean('is_online')->default(false);
            $table->string('online_link')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('start_date');
            $table->index('end_date');
            $table->index('is_online');
        });

        Schema::create('public_event_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('public_events')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('public_users')->onDelete('cascade');
            $table->enum('status', ['registered', 'confirmed', 'cancelled', 'attended'])->default('registered');
            $table->timestamp('registered_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['event_id', 'user_id']);
            $table->index('event_id');
            $table->index('user_id');
            $table->index('status');
        });

        Schema::create('public_pages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->string('slug')->unique();
            $table->text('content');
            $table->integer('order')->default(0);
            $table->integer('parent_id')->nullable(true);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index('slug');
            $table->index('is_published');
            $table->index('parent_id');
        });

        Schema::create('public_news', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->string('slug')->unique();
            $table->text('content');
            $table->foreignId('user_id')->constrained('public_users')->onDelete('cascade');
            $table->boolean('is_published')->default(false);
            $table->dateTime('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('slug');
            $table->index('user_id');
            $table->index('is_published');
            $table->index('published_at');
        });

        Schema::create('public_search_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('public_users')->onDelete('cascade');
            $table->string('search_term');
            $table->json('filters')->nullable();
            $table->integer('results_count');
            $table->softDeletes();
            $table->timestamps();
            $table->index('user_id');
            $table->index('search_term');
        });

        Schema::create('public_document_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('public_users')->onDelete('cascade');
            $table->foreignId('record_id')->constrained('public_records')->onDelete('restrict');
            $table->enum('request_type', ['digital', 'physical']);
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->dateTime('processed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('user_id');
            $table->index('record_id');
            $table->index('status');
            $table->index('processed_at');
        });

        Schema::create('public_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_request_id')->constrained('public_document_requests')->onDelete('cascade');
            $table->foreignId('responded_by')->constrained('users')->onDelete('restrict');
            $table->text('instructions')->nullable();
            $table->enum('status', ['draft', 'sent', 'updated'])->default('draft');
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('document_request_id');
            $table->index('responded_by');
            $table->index('status');
            $table->index('sent_at');
        });

        Schema::create('public_response_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('public_response_id')->constrained('public_responses')->onDelete('cascade');
            $table->foreignId('attachment_id')->constrained('attachments')->onDelete('cascade');
            $table->integer('download_count')->default(0);
            $table->dateTime('expires_at')->nullable();
            $table->boolean('is_public')->default(true);
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();
            $table->index('public_response_id');
            $table->index('attachment_id');
            $table->index('expires_at');
            $table->index('uploaded_by');
        });

        Schema::create('public_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('public_users')->onDelete('cascade');
            $table->string('subject');
            $table->text('content');
            $table->enum('status', ['pending', 'reviewed', 'responded'])->default('pending');
            $table->foreignId('related_id')->nullable();
            $table->string('related_type')->nullable();
            $table->integer('rating')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('user_id');
            $table->index('status');
        });

        Schema::create('public_chats', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->boolean('is_group')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index('is_active');
        });

        Schema::create('public_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('public_chats')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('public_users')->onDelete('cascade');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index('chat_id');
            $table->index('user_id');
            $table->index('is_read');
        });

        Schema::create('public_chat_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('public_chats')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('public_users')->onDelete('cascade');
            $table->boolean('is_admin')->default(false);
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['chat_id', 'user_id']);
            $table->index('chat_id');
            $table->index('user_id');
        });

        // SUPPRIMÉ : La définition dupliquée de public_event_registrations qui était à la fin du fichier
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Suppression des tables dans l'ordre inverse pour respecter les contraintes de clés étrangères
        Schema::dropIfExists('public_event_registrations');
        Schema::dropIfExists('public_chat_participants');
        Schema::dropIfExists('public_chat_messages');
        Schema::dropIfExists('public_chats');
        Schema::dropIfExists('public_feedbacks');
        Schema::dropIfExists('public_response_attachments');
        Schema::dropIfExists('public_responses');
        Schema::dropIfExists('public_document_requests');
        Schema::dropIfExists('public_search_logs');
        Schema::dropIfExists('public_news');
        Schema::dropIfExists('public_pages');
        Schema::dropIfExists('public_events');
        Schema::dropIfExists('public_records');
        Schema::dropIfExists('public_templates');
        Schema::dropIfExists('public_users');

        // Ne pas supprimer la table attachments car elle pourrait être utilisée par d'autres modules
    }
};
