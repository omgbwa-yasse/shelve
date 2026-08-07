<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * L'historique de conversation avec l'assistant IA ne doit jamais être
 * supprimé (exigence utilisateur du 2026-08-05) : "Supprimer" une
 * conversation l'archive (masquée de l'onglet Historique) sans jamais
 * appeler `delete()` — voir `AiConversationController::archive()`. Aucune
 * route/­méthode de suppression physique n'existe pour ce modèle.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_conversations', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('context');
        });
    }

    public function down(): void
    {
        Schema::table('ai_conversations', function (Blueprint $table) {
            $table->dropColumn('archived_at');
        });
    }
};
