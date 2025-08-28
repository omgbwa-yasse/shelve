<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing columns or relax types to avoid enum truncation issues
        Schema::table('permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('permissions', 'category')) {
                $table->string('category', 64)->nullable()->after('name');
            }
            if (!Schema::hasColumn('permissions', 'guard_name')) {
                $table->string('guard_name', 64)->nullable()->after('description');
            }
        });

        // If 'category' exists but is an ENUM or a smaller type, relax it to VARCHAR(64)
        try {
            // MySQL-specific: change column type to VARCHAR(64) NULL
            DB::statement("ALTER TABLE permissions MODIFY COLUMN category VARCHAR(64) NULL");
        } catch (\Throwable $e) {
            // Ignore if the database does not support this direct modification
        }
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            if (Schema::hasColumn('permissions', 'guard_name')) {
                $table->dropColumn('guard_name');
            }
            // Keep 'category' column as removing it could break existing data
        });
    }
};
