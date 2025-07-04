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
        Schema::table('mails', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('mails', 'external_sender_organization_id')) {
                $table->foreignId('external_sender_organization_id')->nullable();
                $table->foreign('external_sender_organization_id')
                      ->references('id')
                      ->on('external_organizations')
                      ->onDelete('set null');
            }

            if (!Schema::hasColumn('mails', 'external_recipient_organization_id')) {
                $table->foreignId('external_recipient_organization_id')->nullable();
                $table->foreign('external_recipient_organization_id')
                      ->references('id')
                      ->on('external_organizations')
                      ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mails', function (Blueprint $table) {
            // Drop foreign keys if they exist
            if (Schema::hasColumn('mails', 'external_sender_organization_id')) {
                $table->dropForeign(['external_sender_organization_id']);
                $table->dropColumn('external_sender_organization_id');
            }

            if (Schema::hasColumn('mails', 'external_recipient_organization_id')) {
                $table->dropForeign(['external_recipient_organization_id']);
                $table->dropColumn('external_recipient_organization_id');
            }
        });
    }
};
