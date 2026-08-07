<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cache local des messages synchronisés depuis le serveur IMAP (`EmailSyncService`)
 * ou envoyés via SMTP (`EmailSendService`). Le contenu vient toujours du serveur
 * distant — cette table est un miroir consultable rapidement côté application,
 * pas la source de vérité.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_account_id')->constrained()->cascadeOnDelete();

            // Identité IMAP : uid+folder unique par compte, message_id pour le threading.
            $table->unsignedBigInteger('uid')->nullable();
            $table->string('folder')->default('INBOX'); // INBOX, Sent, Drafts, Trash, ...
            $table->string('message_id')->nullable();
            $table->string('in_reply_to')->nullable();

            $table->string('subject')->nullable();
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->json('to')->nullable();
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();

            $table->longText('body_html')->nullable();
            $table->longText('body_text')->nullable();

            $table->boolean('is_read')->default(false);
            $table->boolean('is_flagged')->default(false);
            $table->boolean('is_draft')->default(false);
            $table->boolean('is_answered')->default(false);
            $table->boolean('has_attachments')->default(false);

            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->unique(['email_account_id', 'folder', 'uid']);
            $table->index('message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_messages');
    }
};
