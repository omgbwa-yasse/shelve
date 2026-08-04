<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workplace_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workplace_id');
            $table->enum('type', ['private', 'group', 'channel'])->default('private');
            $table->string('name', 150)->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('workplace_id')->references('id')->on('workplaces')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['workplace_id', 'type']);
        });

        Schema::create('workplace_conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('user_id');
            $table->string('role', 20)->default('member');
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();
            $table->unique(['conversation_id', 'user_id'], 'wcp_conv_user_unique');
            $table->foreign('conversation_id')->references('id')->on('workplace_conversations')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('workplace_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('user_id');
            $table->text('content');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('conversation_id')->references('id')->on('workplace_conversations')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workplace_messages');
        Schema::dropIfExists('workplace_conversation_participants');
        Schema::dropIfExists('workplace_conversations');
    }
};
