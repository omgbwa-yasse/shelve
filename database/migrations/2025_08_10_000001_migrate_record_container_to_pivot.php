<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('record_container')) {
            // safety: pivot table must exist already per original schema
            return;
        }

        if (Schema::hasColumn('records', 'container_id')) {
            // migrate existing single container linkage into pivot if missing
            DB::table('records')
                ->select('id as record_id', 'container_id', 'user_id')
                ->whereNotNull('container_id')
                ->orderBy('id')
                ->chunk(500, function ($rows) {
                    foreach ($rows as $row) {
                        $exists = DB::table('record_container')
                            ->where('record_id', $row->record_id)
                            ->where('container_id', $row->container_id)
                            ->exists();
                        if (!$exists) {
                            DB::table('record_container')->insert([
                                'record_id' => $row->record_id,
                                'container_id' => $row->container_id,
                                'description' => null,
                                'creator_id' => $row->user_id ?? 1,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                });

            // drop FK then column
            // Drop foreign key if it exists (best effort)
            Schema::table('records', function (Blueprint $table) {
                if (Schema::hasColumn('records', 'container_id')) {
                    // Laravel usually names it records_container_id_foreign
                    try { $table->dropForeign('records_container_id_foreign'); } catch (\Throwable $e) {}
                    try { $table->dropForeign(['container_id']); } catch (\Throwable $e) {}
                }
            });
            // Drop column after FK removal
            Schema::table('records', function (Blueprint $table) {
                if (Schema::hasColumn('records', 'container_id')) {
                    $table->dropColumn('container_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('records', 'container_id')) {
            Schema::table('records', function (Blueprint $table) {
                $table->unsignedBigInteger('container_id')->nullable()->after('parent_id');
            });
            Schema::table('records', function (Blueprint $table) {
                // Recreate FK if possible
                $table->foreign('container_id')->references('id')->on('containers')->onDelete('cascade');
            });
        }
        // Note: we cannot safely remove data from pivot; leaving as-is
    }
};
