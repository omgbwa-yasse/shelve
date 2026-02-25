<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add organisation-related indexes for Communications, Slips, and Mails tables
     * to support efficient organisation scoping queries.
     */
    public function up(): void
    {
        // Communications - dual org (operator/user)
        Schema::table('communications', function (Blueprint $table) {
            $table->index('operator_organisation_id', 'idx_comm_operator_org');
            $table->index('user_organisation_id', 'idx_comm_user_org');
            $table->index(['operator_organisation_id', 'user_organisation_id'], 'idx_comm_dual_org');
        });

        // Slips - dual org (officer/user)
        Schema::table('slips', function (Blueprint $table) {
            $table->index('officer_organisation_id', 'idx_slip_officer_org');
            $table->index('user_organisation_id', 'idx_slip_user_org');
            $table->index(['officer_organisation_id', 'user_organisation_id'], 'idx_slip_dual_org');
        });

        // Mails - triple org (sender/recipient/assigned)
        Schema::table('mails', function (Blueprint $table) {
            $table->index('sender_organisation_id', 'idx_mail_sender_org');
            $table->index('recipient_organisation_id', 'idx_mail_recipient_org');
            // assigned_organisation_id already has an index from the add_assigned_organisation migration
        });

        // Reservations - dual org (operator/user) - same pattern as communications
        Schema::table('reservations', function (Blueprint $table) {
            $table->index('operator_organisation_id', 'idx_reservation_operator_org');
            $table->index('user_organisation_id', 'idx_reservation_user_org');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex('idx_reservation_operator_org');
            $table->dropIndex('idx_reservation_user_org');
        });

        Schema::table('mails', function (Blueprint $table) {
            $table->dropIndex('idx_mail_sender_org');
            $table->dropIndex('idx_mail_recipient_org');
        });

        Schema::table('slips', function (Blueprint $table) {
            $table->dropIndex('idx_slip_officer_org');
            $table->dropIndex('idx_slip_user_org');
            $table->dropIndex('idx_slip_dual_org');
        });

        Schema::table('communications', function (Blueprint $table) {
            $table->dropIndex('idx_comm_operator_org');
            $table->dropIndex('idx_comm_user_org');
            $table->dropIndex('idx_comm_dual_org');
        });
    }
};
