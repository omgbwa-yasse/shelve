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
        Schema::table('record_keyword', function (Blueprint $table) {
            // Add timestamps if they don't exist
            if (!Schema::hasColumn('record_keyword', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('record_keyword', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
};
