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
        Schema::table('public_responses', function (Blueprint $table) {
            // Renommer responded_by en user_id si il existe et que user_id n'existe pas
            if (Schema::hasColumn('public_responses', 'responded_by') && !Schema::hasColumn('public_responses', 'user_id')) {
                $table->renameColumn('responded_by', 'user_id');
            }

            // Ajouter user_id si ni responded_by ni user_id n'existent
            if (!Schema::hasColumn('public_responses', 'user_id') && !Schema::hasColumn('public_responses', 'responded_by')) {
                $table->foreignId('user_id')->nullable()->after('document_request_id')->constrained('users')->onDelete('set null');
            }

            // Renommer instructions en content si il existe et que content n'existe pas
            if (Schema::hasColumn('public_responses', 'instructions') && !Schema::hasColumn('public_responses', 'content')) {
                $table->renameColumn('instructions', 'content');
            }

            // Ajouter content si ni instructions ni content n'existent
            if (!Schema::hasColumn('public_responses', 'content') && !Schema::hasColumn('public_responses', 'instructions')) {
                $table->text('content')->after('document_request_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('public_responses', function (Blueprint $table) {
            if (Schema::hasColumn('public_responses', 'content')) {
                $table->renameColumn('content', 'instructions');
            }
            if (Schema::hasColumn('public_responses', 'user_id')) {
                $table->renameColumn('user_id', 'responded_by');
            }
        });
    }
};
