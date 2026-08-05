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
        Schema::table('task_attachments', function (Blueprint $table) {
            // Drop the index first
            $table->dropIndex('idx_attachment_attachable');
            // Drop the enum column
            $table->dropColumn('attachable_type');
        });

        Schema::table('task_attachments', function (Blueprint $table) {
            $table->string('attachable_type')->after('task_id');
            // Re-add the index
            $table->index(['attachable_type', 'attachable_id'], 'idx_attachment_attachable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_attachments', function (Blueprint $table) {
            $table->dropIndex('idx_attachment_attachable');
            $table->dropColumn('attachable_type');
        });

        Schema::table('task_attachments', function (Blueprint $table) {
            $table->enum('attachable_type', ['Book', 'RecordPhysical', 'Document', 'Folder', 'Artifact', 'Collection'])->after('task_id');
            $table->index(['attachable_type', 'attachable_id'], 'idx_attachment_attachable');
        });
    }
};
