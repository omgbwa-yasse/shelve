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
        Schema::table('public_feedbacks', function (Blueprint $table) {
            // Ajouter le champ title et modifier subject
            $table->string('title')->after('user_id')->nullable();
            // Ajouter les nouveaux champs
            $table->enum('type', ['bug', 'feature', 'improvement', 'other'])->after('content')->default('other');
            $table->enum('priority', ['low', 'medium', 'high'])->after('type')->default('medium');
            // Modifier le statut pour correspondre aux nouvelles valeurs
            $table->dropColumn('status');
        });

        Schema::table('public_feedbacks', function (Blueprint $table) {
            $table->enum('status', ['new', 'in_progress', 'resolved', 'closed'])->after('priority')->default('new');
            // Ajouter les champs de contact pour l'API
            $table->string('contact_name')->nullable()->after('status');
            $table->string('contact_email')->nullable()->after('contact_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('public_feedbacks', function (Blueprint $table) {
            $table->dropColumn(['title', 'type', 'priority', 'contact_name', 'contact_email']);
            $table->dropColumn('status');
        });

        Schema::table('public_feedbacks', function (Blueprint $table) {
            $table->enum('status', ['pending', 'reviewed', 'responded'])->default('pending');
        });
    }
};
