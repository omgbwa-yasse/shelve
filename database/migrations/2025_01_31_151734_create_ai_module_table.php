<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAiModuleTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('prompts');
        Schema::dropIfExists('ai_agents');
        Schema::dropIfExists('ai_model_configs');


        // Prompts
        Schema::create('prompts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('instruction');
            $table->boolean('is_public')->default(true);
            $table->boolean('is_draft')->default(true);
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_system')->default(false);
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });


        // Input attachments table
        Schema::create('prompt_input_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prompt_id')->constrained('prompts')->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['file', 'text', 'image', 'video', 'audio']); // file, text, image, etc.
            $table->text('description')->nullable();
            $table->string('path')->nullable();
            $table->string('file_crypt')->nullable();
            $table->boolean('is_required')->default(false);
            $table->json('validation_rules')->nullable(); // Store validation rules as JSON
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });



        // Output attachments table
        Schema::create('prompt_output_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prompt_id')->constrained('prompts')->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['file', 'text', 'image', 'video', 'audio']); // file, text, image, etc.
            $table->text('description')->nullable();
            $table->string('path')->nullable();
            $table->string('file_crypt')->nullable();
            $table->json('format_rules')->nullable(); // Store format specifications as JSON
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        // Agents AI
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique(true);
            $table->text('description');
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->date('date_exact')->nullable();
            $table->enum('date_type', ['start_only', 'exact', 'range'])->default('start_only');
            $table->enum('frequence_type', ['day', 'heure', 'min'])->default('day');
            $table->integer('frequence_value');
            $table->foreignId('prompt_id')->constrained('prompts');
            $table->foreignId('user_id')->constrained('users');
            $table->boolean('is_public')->default(true);
            $table->boolean('is_trained')->default(true);
            $table->timestamps();
        });

        Schema::create('model_configs', function (Blueprint $table) {
            $table->id();
            $table->string('model_name');
            $table->string('config_key');
            $table->text('value');
            $table->timestamps();
        });

        // Table des tags
        Schema::create('chat_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Table des conversations
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable();
            $table->enum('status', ['active', 'archived', 'deleted'])->default('active');
            $table->timestamps();

            $table->index('user_id');
        });


        // Table des messages
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chat_conversations')->onDelete('cascade');
            $table->enum('role', ['user', 'assistant']);
            $table->text('content');
            $table->integer('tokens_used')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index('conversation_id');
        });



        // Table de liaison messages-tags
        Schema::create('chat_message_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('chat_messages')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('chat_tags')->onDelete('cascade');
            $table->unsignedTinyInteger('relevance_score')->comment('Score between 1 and 10');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['message_id', 'tag_id']);
            $table->index('message_id');
            $table->index('tag_id');
        });

    }

    public function down()
    {
        Schema::dropIfExists('ai_model_configs');
        Schema::dropIfExists('ai_agents');
        Schema::dropIfExists('prompts');
        Schema::dropIfExists('chat_message_tags');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
        Schema::dropIfExists('chat_tags');
    }
}
