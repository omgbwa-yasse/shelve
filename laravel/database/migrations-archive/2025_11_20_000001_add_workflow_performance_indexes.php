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
        Schema::table('tasks', function (Blueprint $table) {
            // Add performance indexes for frequently queried fields
            $table->index('status', 'idx_task_status_perf');
            $table->index('priority', 'idx_task_priority_perf');
            $table->index('due_date', 'idx_task_due_date_perf');
            $table->index(['status', 'assigned_to'], 'idx_task_status_assigned');
            $table->index(['status', 'due_date'], 'idx_task_status_due');
        });

        Schema::table('workflow_instances', function (Blueprint $table) {
            $table->index('status', 'idx_workflow_instance_status');
            $table->index(['status', 'started_at'], 'idx_workflow_instance_status_started');
        });

        Schema::table('workflow_definitions', function (Blueprint $table) {
            $table->index('status', 'idx_workflow_def_status');
            $table->index(['status', 'created_at'], 'idx_workflow_def_status_created');
        });

        Schema::table('task_reminders', function (Blueprint $table) {
            $table->index(['is_sent', 'remind_at'], 'idx_reminder_sent_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('idx_task_status_perf');
            $table->dropIndex('idx_task_priority_perf');
            $table->dropIndex('idx_task_due_date_perf');
            $table->dropIndex('idx_task_status_assigned');
            $table->dropIndex('idx_task_status_due');
        });

        Schema::table('workflow_instances', function (Blueprint $table) {
            $table->dropIndex('idx_workflow_instance_status');
            $table->dropIndex('idx_workflow_instance_status_started');
        });

        Schema::table('workflow_definitions', function (Blueprint $table) {
            $table->dropIndex('idx_workflow_def_status');
            $table->dropIndex('idx_workflow_def_status_created');
        });

        Schema::table('task_reminders', function (Blueprint $table) {
            $table->dropIndex('idx_reminder_sent_date');
        });
    }
};
