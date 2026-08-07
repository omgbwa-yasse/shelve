<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sandbox d'exécution Python pour l'assistant IA (D14).
 *
 * Un sandbox = un workspace sur disque structuré selon un "pattern"
 * (ex. `standard` : input/ core/ reference/ output/ logs/). L'IA écrit du
 * code Python dans `core/`, l'exécute, et les fichiers produits dans
 * `output/` sont récupérés à la clôture (`sandbox_close`).
 *
 * Un sandbox est rattaché facultativement à une conversation AI
 * (`ai_conversations`) : chaque conversation peut ouvrir/rouvrir un sandbox.
 *
 * Les fichiers produits ne sont jamais stockés en base — `ai_sandbox_files`
 * ne contient que des métadonnées + le chemin relatif dans le workspace.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_sandboxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->nullable()->constrained('organisations')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained('ai_conversations')->nullOnDelete();
            $table->string('name', 190)->nullable();
            $table->string('pattern', 30)->default('standard'); // standard|...
            $table->string('engine', 20)->default('local');     // local|docker
            $table->string('status', 20)->default('created');   // created|running|success|error|expired
            $table->string('folder', 100)->unique();
            $table->longText('last_output')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['organisation_id', 'user_id']);
            $table->index(['conversation_id', 'status']);
        });

        Schema::create('ai_sandbox_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sandbox_id')->constrained('ai_sandboxes')->cascadeOnDelete();
            $table->string('section', 20); // input|core|reference|output|logs
            $table->string('path', 255);
            $table->string('name', 255);
            $table->unsignedBigInteger('size')->default(0);
            $table->string('mime', 120)->nullable();
            $table->char('hash', 64)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['sandbox_id', 'section']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_sandbox_files');
        Schema::dropIfExists('ai_sandboxes');
    }
};
