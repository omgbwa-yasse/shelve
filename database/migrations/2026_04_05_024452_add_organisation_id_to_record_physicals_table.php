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
        if (!Schema::hasColumn('record_physicals', 'organisation_id')) {
            Schema::table('record_physicals', function (Blueprint $table) {
                $table->foreignId('organisation_id')->nullable()->after('user_id')->constrained('organisations')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('record_physicals', 'organisation_id')) {
            Schema::table('record_physicals', function (Blueprint $table) {
                $table->dropConstrainedForeignId('organisation_id');
            });
        }
    }
};
