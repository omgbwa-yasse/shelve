<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add organisation_id to workflow_definitions, workflow_instances, and tasks tables.
     * Backfill from creator/starter user's current_organisation_id.
     */
    public function up(): void
    {
        // 1. Add nullable column first (to allow backfill)
        Schema::table('workflow_definitions', function (Blueprint $table) {
            $table->unsignedBigInteger('organisation_id')->nullable()->after('id');
        });

        Schema::table('workflow_instances', function (Blueprint $table) {
            $table->unsignedBigInteger('organisation_id')->nullable()->after('id');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('organisation_id')->nullable()->after('id');
        });

        // 2. Backfill organisation_id from the creator/starter user
        DB::statement('
            UPDATE workflow_definitions wd
            JOIN users u ON u.id = wd.created_by
            SET wd.organisation_id = u.current_organisation_id
            WHERE wd.organisation_id IS NULL
              AND u.current_organisation_id IS NOT NULL
        ');

        DB::statement('
            UPDATE workflow_instances wi
            JOIN users u ON u.id = wi.started_by
            SET wi.organisation_id = u.current_organisation_id
            WHERE wi.organisation_id IS NULL
              AND u.current_organisation_id IS NOT NULL
        ');

        DB::statement('
            UPDATE tasks t
            JOIN users u ON u.id = t.created_by
            SET t.organisation_id = u.current_organisation_id
            WHERE t.organisation_id IS NULL
              AND u.current_organisation_id IS NOT NULL
        ');

        // 3. For any remaining NULL values, try to get org from the first available organisation
        $defaultOrgId = DB::table('organisations')->value('id');

        if ($defaultOrgId) {
            DB::table('workflow_definitions')
                ->whereNull('organisation_id')
                ->update(['organisation_id' => $defaultOrgId]);

            DB::table('workflow_instances')
                ->whereNull('organisation_id')
                ->update(['organisation_id' => $defaultOrgId]);

            DB::table('tasks')
                ->whereNull('organisation_id')
                ->update(['organisation_id' => $defaultOrgId]);
        }

        // 4. Make column NOT NULL and add FK + index
        Schema::table('workflow_definitions', function (Blueprint $table) {
            $table->unsignedBigInteger('organisation_id')->nullable(false)->change();
            $table->foreign('organisation_id', 'fk_workflow_def_org')
                ->references('id')->on('organisations')->onDelete('cascade');
            $table->index('organisation_id', 'idx_workflow_def_org');
        });

        Schema::table('workflow_instances', function (Blueprint $table) {
            $table->unsignedBigInteger('organisation_id')->nullable(false)->change();
            $table->foreign('organisation_id', 'fk_workflow_inst_org')
                ->references('id')->on('organisations')->onDelete('cascade');
            $table->index('organisation_id', 'idx_workflow_inst_org');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('organisation_id')->nullable(false)->change();
            $table->foreign('organisation_id', 'fk_task_org')
                ->references('id')->on('organisations')->onDelete('cascade');
            $table->index('organisation_id', 'idx_task_org');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign('fk_task_org');
            $table->dropIndex('idx_task_org');
            $table->dropColumn('organisation_id');
        });

        Schema::table('workflow_instances', function (Blueprint $table) {
            $table->dropForeign('fk_workflow_inst_org');
            $table->dropIndex('idx_workflow_inst_org');
            $table->dropColumn('organisation_id');
        });

        Schema::table('workflow_definitions', function (Blueprint $table) {
            $table->dropForeign('fk_workflow_def_org');
            $table->dropIndex('idx_workflow_def_org');
            $table->dropColumn('organisation_id');
        });
    }
};
