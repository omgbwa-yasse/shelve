<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modes de l'assistant IA (voir demande utilisateur du 2026-08-05) : manuel
 * (défaut, confirmation systématique), edit (édits pré-approuvés, création/
 * suppression toujours confirmées), plan (ne propose qu'un plan, n'exécute
 * jamais), autonome (agit dans la limite des permissions de l'agent, sans
 * confirmation répétée). Le mode est une propriété de la conversation —
 * modifiable en cours de fil (voir `AiConversationController::sendMessage`).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_conversations', function (Blueprint $table) {
            $table->string('mode', 20)->default('manuel')->after('archived_at');
        });
    }

    public function down(): void
    {
        Schema::table('ai_conversations', function (Blueprint $table) {
            $table->dropColumn('mode');
        });
    }
};
