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
        Schema::table('record_books', function (Blueprint $table) {
            $table->dropColumn('format');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('record_books', function (Blueprint $table) {
            $table->string('format')
                ->nullable()
                ->after('format_id')
                ->comment('Book format (deprecated - use format_id)');
        });
    }
};
